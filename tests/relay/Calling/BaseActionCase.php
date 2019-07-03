<?php

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Client;
use SignalWire\Relay\Calling\Calling;
use SignalWire\Relay\Calling\Call;

abstract class RelayCallingBaseActionCase extends TestCase
{
  const UUID = 'e36f227c-2946-11e8-b467-0ed5f89f718b';
  protected $client;
  protected $calling;
  protected $call;

  protected function setUp() {
    $this->mockUuid();
    $this->setUpClient();
    $this->setUpCall();
  }

  protected function tearDown() {
    unset($this->client, $this->call);
    SignalWire\Handler::deRegisterAll('signalwire_calling_proto');
    \Ramsey\Uuid\Uuid::setFactory(new \Ramsey\Uuid\UuidFactory());
  }

  protected function mockUuid() {
    $factory = $this->createMock(\Ramsey\Uuid\UuidFactoryInterface::class);
    $factory->method('uuid4')
      ->will($this->returnValue(\Ramsey\Uuid\Uuid::fromString(self::UUID)));
    \Ramsey\Uuid\Uuid::setFactory($factory);
  }

  protected function setUpClient() {
    $this->client = new Client(array('host' => 'host', 'project' => 'project', 'token' => 'token'));
    $this->client->connection = $this->createMock(SignalWire\Relay\Connection::class, ['send']);
  }

  protected function setUpCall() {
    $this->calling = new Calling($this->client);
    $this->calling->protocol = 'signalwire_calling_proto';

    $options = (object)[
      'device' => (object)[
        'type' => 'phone',
        'params' => (object)['from_number' => '234', 'to_number' => '456', 'timeout' => 20]
      ]
    ];
    $this->call = new Call($this->calling, $options);
  }

  protected function _setCallReady() {
    $this->call->id = 'call-id';
    $this->call->nodeId = 'node-id';
  }
}
