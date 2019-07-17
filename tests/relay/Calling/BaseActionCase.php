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
    $this->client->relayProtocol = 'signalwire_calling_proto';
  }

  protected function setUpCall() {
    $this->calling = new Calling($this->client);

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

  protected function _mockSuccessResponse($msg) {
    $success = \React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message","control_id":"' . self::UUID . '"}}'));
    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($success);
  }

  protected function _mockFailResponse($msg) {
    $fail = \React\Promise\reject(json_decode('{"result":{"code":"400","message":"some error","control_id":"' . self::UUID . '"}}'));
    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($fail);
  }
}
