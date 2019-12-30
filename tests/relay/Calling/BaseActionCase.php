<?php

require_once dirname(__FILE__) . '/../BaseRelayCase.php';

use SignalWire\Relay\Calling\Calling;
use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\Devices\DeviceFactory;

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
      'targets' => \SignalWire\prepareDevices([
        [ 'type' => 'phone', 'from' => '234', 'to' => '456', 'timeout' => 20 ],
        [
          [ 'type' => 'phone', 'from' => '234', 'to' => '789', 'timeout' => 30 ],
          [ 'type' => 'agora', 'from' => '234', 'to' => '456', 'app_id' => 'appid', 'channel' => '1111' ],
          [ 'type' => 'sip', 'from' => 'user@domain.com', 'to' => 'user@example.com', 'timeout' => 20 ]
        ],
        [ 'type' => 'webrtc', 'from' => 'user@domain.com', 'to' => '3500@conf.signalwire.com', 'codecs' => ['OPUS'] ]
      ])
    ];
    $this->call = new Call($this->calling, $options);
  }

  protected function _setCallReady() {
    $this->call->id = 'call-id';
    $this->call->nodeId = 'node-id';
    $this->call->device = DeviceFactory::create(['type' => 'phone', 'params' => ['to_number' => '+99900000000', 'from_number' => '+88800000000', 'timeout' => 20]]);
    $this->call->type = 'phone';
    $this->call->from = '+88800000000';
    $this->call->to = '+99900000000';
    $this->call->timeout = 20;
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
