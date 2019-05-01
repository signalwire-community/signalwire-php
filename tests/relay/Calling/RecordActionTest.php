<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Calling\RecordAction;
use SignalWire\Messages\Execute;

class RelayCallingRecordActionTest extends RelayCallingBaseActionCase
{
  protected function setUp() {
    parent::setUp();
    $this->_setCallReady();
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

    $this->client->connection->expects($this->once())
      ->method('send')
      ->with($msg)
      ->willReturn(\React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message"}}')));

    $action = new RecordAction($this->call, 'control-id');
    $res = $action->stop();
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }
}
