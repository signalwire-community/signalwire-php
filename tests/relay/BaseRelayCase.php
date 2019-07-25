<?php

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Client;

abstract class BaseRelayCase extends TestCase
{
  protected $client;

  protected function setUp() {
    $this->client = new Client(array('project' => 'project', 'token' => 'token'));
    $this->client->relayProtocol = 'relay-proto';
  }

  public function tearDown() {
    unset($this->client);
  }

  protected function _mockResponse($responses) {
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

  protected function _mockSendNotToBeCalled() {
    $stub = $this->createMock(SignalWire\Relay\Connection::class, ['send']);
    $stub->expects($this->never())->method('send');
    $this->client->connection = $stub;
  }

}
