<?php

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Client;
use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\RecordAction;
use SignalWire\Messages\Execute;

class RelayCallingRecordActionTest extends TestCase
{
  const UUID = 'e36f227c-2946-11e8-b467-0ed5f89f718b';
  protected $stub;
  protected $action;

  protected function setUp() {
    $this->_mockUuid();
    $responseProto = json_decode('{"requester_nodeid":"ad490dc4-550a-4742-929d-b86fdf8958ef","responder_nodeid":"b0007713-071d-45f9-88aa-302d14e1251c","result":{"protocol":"signalwire_calling_proto"}}');
    $responseSubscr = json_decode('{"protocol":"signalwire_calling_proto","command":"add","subscribe_channels":["notifications"]}');
    $methodResponse = json_decode('{"requester_nodeid":"0ff2d880-c420-48c4-89b8-6d9d540d3b80","responder_nodeid":"1a9c9e34-892c-435c-9749-1f9e584bdae1","result":{"code":"200","message":"message"}}');

    $this->stub = $this->createMock(SignalWire\Relay\Connection::class, ['send']);
    $this->stub->method('send')->will($this->onConsecutiveCalls(
      \React\Promise\resolve($responseProto),
      \React\Promise\resolve($responseSubscr),
      \React\Promise\resolve($methodResponse)
    ));

    $client = new Client(array('host' => 'host', 'project' => 'project', 'token' => 'token'));
    $client->connection = $this->stub;

    $this->stub->expects($this->exactly(3))->method('send');

    $options = (object)[
      'device' => (object)[
        'type' => 'phone',
        'params' => (object)['from_number' => '234', 'to_number' => '456', 'timeout' => 20]
      ]
    ];
    $call = new Call($client->calling, $options);
    $call->id = 'call-id';
    $call->nodeId = 'node-id';

    $this->action = new RecordAction($call, 'control-id');
  }

  public function tearDown() {
    unset($this->call);
    SignalWire\Handler::deRegisterAll('signalwire_calling_proto');
    \Ramsey\Uuid\Uuid::setFactory(new \Ramsey\Uuid\UuidFactory());
  }

  public function _mockUuid() {
    $factory = $this->createMock(\Ramsey\Uuid\UuidFactoryInterface::class);
    $factory->method('uuid4')
      ->will($this->returnValue(\Ramsey\Uuid\Uuid::fromString(self::UUID)));
    \Ramsey\Uuid\Uuid::setFactory($factory);
  }

  public function testStop(): void {
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.record.stop',
      'params' => [
        'node_id' => 'node-id',
        'call_id' => 'call-id',
        'control_id' => 'control-id'
      ]
    ]);

    $this->stub->expects($this->once())->method('send')->with($msg);

    $res = $this->action->stop();
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }
}
