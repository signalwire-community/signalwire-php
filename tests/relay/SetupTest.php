<?php

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Client;
use SignalWire\Relay\Setup;

class RelaySetupTest extends TestCase
{
  protected $client;

  protected function setUp() {
    $this->client = new Client(array('project' => 'project', 'token' => 'token'));
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

  public function testProtocolSetup(): void {
    $responseProto = json_decode('{"requester_nodeid":"ad490dc4-550a-4742-929d-b86fdf8958ef","responder_nodeid":"b0007713-071d-45f9-88aa-302d14e1251c","result":{"protocol":"signalwire_calling_proto"}}');
    $responseSubscr = json_decode('{"protocol":"signalwire_calling_proto","command":"add","subscribe_channels":["notifications"]}');
    $this->_mockResponse([$responseProto, $responseSubscr]);

    Setup::protocol($this->client)->then(function (String $protocol) {
      $this->assertEquals('signalwire_calling_proto', $protocol);
      $this->assertArrayHasKey('signalwire_calling_protonotifications', $this->client->subscriptions);
    });
  }
}
