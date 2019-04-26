<?php

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Client;
use SignalWire\Messages\Subscription;

class RelayClientTest extends TestCase
{
  protected $client;
  private $_opts = array("host" => "host", "project" => "project", "token" => "token");

  protected function setUp() {
    $this->client = new Client(array('host' => 'host', 'project' => 'project', 'token' => 'token'));
  }

  public function tearDown() {
    unset($this->client);
  }

  private function _mockResponse($responses) {
    $stub = $this->createMock(SignalWire\Relay\Connection::class, ['send']);
    if (!is_array($responses)) {
      $responses = [$responses];
    }
    foreach ($responses as $i => $r) {
      $stub->expects($this->at($i))
        ->method('send')
        ->will($this->returnValue(\React\Promise\resolve($r)));
    }

    $this->client->connection = $stub;
  }

  // Testing Subscribe
  public function testSubscribeWithSuccessResponse(): void {
    $this->_mockResponse(json_decode('{"protocol":"proto","command":"add","subscribe_channels":["c1","c2"]}'));

    $this->client->subscribe('proto', array('c1', 'c2'));

    $this->assertArrayHasKey('protoc1', $this->client->subscriptions);
    $this->assertArrayHasKey('protoc2', $this->client->subscriptions);
  }

  public function testSubscribeWithFailedResponse(): void {
    $this->_mockResponse(json_decode('{"protocol":"proto","command":"add","failed_channels":["c1","c2"]}'));
    $this->client->subscribe('proto', array('c1', 'c2'));

    $this->assertCount(0, $this->client->subscriptions);
  }

  public function testSubscribeWithBothResponse(): void {
    $this->_mockResponse(json_decode('{"protocol":"proto","command":"add","subscribe_channels":["c1"],"failed_channels":["c2"]}'));
    $this->client->subscribe('proto', array('c1', 'c2'));

    $this->assertArrayHasKey('protoc1', $this->client->subscriptions);
    $this->assertArrayNotHasKey('protoc2', $this->client->subscriptions);
  }

  public function testSubscribeWithHandler(): void {
    $this->_mockResponse(json_decode('{"protocol":"proto","command":"add","subscribe_channels":["notifications"]}'));
    $fn = function($data) {};
    $this->client->subscribe('proto', array('notifications'), $fn);

    $this->assertArrayHasKey('protonotifications', $this->client->subscriptions);
    $this->assertTrue(SignalWire\Handler::isQueued('proto', 'notifications'));
    $this->assertEquals(SignalWire\Handler::queueCount('proto', 'notifications'), 1);
  }

  public function testCallingSetup(): void {
    $responseProto = json_decode('{"requester_nodeid":"ad490dc4-550a-4742-929d-b86fdf8958ef","responder_nodeid":"b0007713-071d-45f9-88aa-302d14e1251c","result":{"protocol":"signalwire_calling_proto"}}');
    $responseSubscr = json_decode('{"protocol":"signalwire_calling_proto","command":"add","subscribe_channels":["notifications"]}');
    $this->_mockResponse([$responseProto, $responseSubscr]);
    $relayInstance = $this->client->calling;

    $this->assertArrayHasKey('signalwire_calling_protonotifications', $this->client->subscriptions);
    $this->assertEquals(SignalWire\Handler::queueCount('signalwire_calling_proto', 'notifications'), 1);
  }
}
