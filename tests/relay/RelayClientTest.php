<?php

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Client;
use SignalWire\Messages\Subscription;

class RelayClientTest extends TestCase
{
  protected $client;
  private $_opts = array("host" => "host", "project" => "project", "token" => "token");

  // protected function setUp() {
  //   mockUuid();
  // }

  public function tearDown() {
    \Mockery::close();
    unset($this->client);
  }

  private function initClient() {
    $this->client = new Client($this->_opts);
  }

  // Testing Subscribe
  public function testSubscribeWithSuccessResponse(): void {
    $response = json_decode('{"protocol":"proto","command":"add","subscribe_channels":["c1","c2"]}');
    mockConnectionSend([$response]);
    $this->initClient();
    $this->client->subscribe('proto', array('c1', 'c2'));

    $this->assertArrayHasKey('protoc1', $this->client->subscriptions);
    $this->assertArrayHasKey('protoc2', $this->client->subscriptions);
  }

  public function testSubscribeWithFailedResponse(): void {
    $response = json_decode('{"protocol":"proto","command":"add","failed_channels":["c1","c2"]}');
    mockConnectionSend([$response]);
    $this->initClient();
    $this->client->subscribe('proto', array('c1', 'c2'));

    $this->assertCount(0, $this->client->subscriptions);
  }

  public function testSubscribeWithBothResponse(): void {
    $response = json_decode('{"protocol":"proto","command":"add","subscribe_channels":["c1"],"failed_channels":["c2"]}');
    mockConnectionSend([$response]);
    $this->initClient();
    $this->client->subscribe('proto', array('c1', 'c2'));

    $this->assertArrayHasKey('protoc1', $this->client->subscriptions);
    $this->assertArrayNotHasKey('protoc2', $this->client->subscriptions);
  }

  public function testSubscribeWithHandler(): void {
    $response = json_decode('{"protocol":"proto","command":"add","subscribe_channels":["notifications"]}');
    mockConnectionSend([$response]);
    $this->initClient();
    $fn = function($data) {};
    $this->client->subscribe('proto', array('notifications'), $fn);

    $this->assertArrayHasKey('protonotifications', $this->client->subscriptions);
    $this->assertTrue(SignalWire\Handler::isQueued('proto', 'notifications'));
    $this->assertEquals(SignalWire\Handler::queueCount('proto', 'notifications'), 1);
  }

  public function testCallingSetup(): void {
    $responseProto = json_decode('{"requester_nodeid":"ad490dc4-550a-4742-929d-b86fdf8958ef","responder_nodeid":"b0007713-071d-45f9-88aa-302d14e1251c","result":{"protocol":"signalwire_calling_proto"}}');
    $responseSubscr = json_decode('{"protocol":"signalwire_calling_proto","command":"add","subscribe_channels":["notifications"]}');
    mockConnectionSend([$responseProto, $responseSubscr]);
    $this->initClient();
    $relayInstance = $this->client->calling;

    $this->assertArrayHasKey('signalwire_calling_protonotifications', $this->client->subscriptions);
    $this->assertEquals(SignalWire\Handler::queueCount('signalwire_calling_proto', 'notifications'), 1);
  }
}
