<?php

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Client;
use Clue\React\Block;

class RelayClientTest extends TestCase
{
  protected $client;
  private $_opts = array("host" => "host", "project" => "project", "token" => "token");

  // protected function setUp() {
  // }

  public function tearDown() {
    Mockery::close();
    unset($this->client);
  }

  private function initClient() {
    $this->client = new Client($this->_opts);
  }

  // Testing Subscribe
  public function testSubscribeWithSuccessResponse(): void {
    $response = json_decode('{"jsonrpc":"2.0","id":"1fa5cef5-9e20-4600-8ee1-ddb315c64cae","result":{"protocol":"proto","command":"add","subscribe_channels":["c1","c2"]}}');
    mockConnectionSend([$response]);
    $this->initClient();

    $promise = $this->client->subscribe('proto', array('c1', 'c2'));
    $result = Block\await($promise, getLoop());

    $this->assertArrayHasKey('protoc1', $this->client->subscriptions);
    $this->assertArrayHasKey('protoc2', $this->client->subscriptions);
  }

  public function testSubscribeWithFailedResponse(): void {
    $response = json_decode('{"jsonrpc":"2.0","id":"1fa5cef5-9e20-4600-8ee1-ddb315c64cae","result":{"protocol":"proto","command":"add","failed_channels":["c1","c2"]}}');
    mockConnectionSend([$response]);
    $this->initClient();

    $promise = $this->client->subscribe('proto', array('c1', 'c2'));
    $result = Block\await($promise, getLoop());

    $this->assertCount(0, $this->client->subscriptions);
  }

  public function testSubscribeWithBothResponse(): void {
    $response = json_decode('{"jsonrpc":"2.0","id":"1fa5cef5-9e20-4600-8ee1-ddb315c64cae","result":{"protocol":"proto","command":"add","subscribe_channels":["c1"],"failed_channels":["c2"]}}');
    mockConnectionSend([$response]);
    $this->initClient();

    $promise = $this->client->subscribe('proto', array('c1', 'c2'));
    $result = Block\await($promise, getLoop());

    $this->assertArrayHasKey('protoc1', $this->client->subscriptions);
    $this->assertArrayNotHasKey('protoc2', $this->client->subscriptions);
  }

  public function testSubscribeWithHandler(): void {
    $response = json_decode('{"jsonrpc":"2.0","id":"1fa5cef5-9e20-4600-8ee1-ddb315c64cae","result":{"protocol":"proto","command":"add","subscribe_channels":["notifications"]}}');
    mockConnectionSend([$response]);
    $this->initClient();

    $fn = function($data) {};
    $promise = $this->client->subscribe('proto', array('notifications'), $fn);
    $result = Block\await($promise, getLoop());

    $this->assertArrayHasKey('protonotifications', $this->client->subscriptions);
    $this->assertTrue(SignalWire\Handler::isQueued('proto', 'notifications'));
    $this->assertEquals(SignalWire\Handler::queueCount('proto', 'notifications'), 1);
  }

  public function testCallingSetup(): void {
    $responseProto = json_decode('{"jsonrpc":"2.0","id":"eb80666c-b73f-4d06-80b6-95efd4bb23f0","result":{"requester_nodeid":"ad490dc4-550a-4742-929d-b86fdf8958ef","responder_nodeid":"b0007713-071d-45f9-88aa-302d14e1251c","result":{"protocol":"signalwire_calling_proto"}}}');
    $responseSubscr = json_decode('{"jsonrpc":"2.0","id":"870a5a76-8416-4b98-8331-22314f9b6c90","result":{"protocol":"signalwire_calling_proto","command":"add","subscribe_channels":["notifications"]}}');
    mockConnectionSend([$responseProto, $responseSubscr]);
    $this->initClient();

    $relayInstance = $this->client->calling;
    $proto = Block\await($relayInstance->ready, getLoop());

    $this->assertEquals($proto, 'signalwire_calling_proto');
    $this->assertArrayHasKey('signalwire_calling_protonotifications', $this->client->subscriptions);
    $this->assertTrue(SignalWire\Handler::isQueued('signalwire_calling_proto', 'notifications'));
    $this->assertEquals(SignalWire\Handler::queueCount('signalwire_calling_proto', 'notifications'), 1);
  }
}
