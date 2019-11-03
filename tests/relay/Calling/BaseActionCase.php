<?php

require_once dirname(__FILE__) . '/../BaseRelayCase.php';

use SignalWire\Relay\Calling\Calling;
use SignalWire\Relay\Calling\Call;

abstract class RelayCallingBaseActionCase extends BaseRelayCase
{
  protected $client;
  protected $calling;
  protected $call;

  protected function setUp() {
    parent::setUp();
    $this->setUpClient();
    $this->setUpCall();
  }

  public function tearDown() {
    unset($this->client, $this->call);
    parent::tearDown();
  }

  protected function setUpClient() {
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

  protected function _mockSuccessResponse($msg, $success = null) {
    if (is_null($success)) {
      $success = json_decode('{"result":{"code":"200","message":"message","control_id":"' . self::UUID . '"}}');
    }
    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn(\React\Promise\resolve($success));
  }

  protected function _mockFailResponse($msg, $fail = null) {
    if (is_null($fail)) {
      $fail = json_decode('{"result":{"code":"400","message":"some error","control_id":"' . self::UUID . '"}}');
    }
    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn(\React\Promise\reject($fail));
  }
}
