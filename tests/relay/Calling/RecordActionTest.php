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
    $this->action = new RecordAction($this->call);
  }

  public function testStop(): void {
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.record.stop',
      'params' => [
        'node_id' => 'node-id',
        'call_id' => 'call-id',
        'control_id' => self::UUID
      ]
    ]);

    $this->client->connection->expects($this->once())
      ->method('send')
      ->with($msg)
      ->willReturn(\React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message"}}')));

    $this->action->stop()->done(function($result) {
      $this->assertEquals($result->code, '200');
    });
  }

  public function testUpdateWithRecording(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","state":"recording","url":"record.mp3","duration":20.0,"size":123456788,"record":{"audio":{"format":"mp3","stereo":false,"direction":"listen"}}}');

    $this->action->update($msg);

    $this->assertFalse($this->action->finished);
    $this->assertEquals($this->action->state, 'recording');
    $this->assertNull($this->action->result);
  }

  public function testUpdateWithNoInput(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","state":"no_input","url":"record.mp3","duration":20.0,"size":123456788,"record":{"audio":{"format":"mp3","stereo":false,"direction":"both"}}}');

    $this->action->update($msg);

    $this->assertTrue($this->action->finished);
    $this->assertEquals($this->action->state, 'no_input');
    $this->assertInstanceOf('SignalWire\Relay\Calling\RecordResult', $this->action->result);
  }

  public function testUpdateWithFinished(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","state":"finished","url":"record.mp3","duration":20.0,"size":123456788,"record":{"audio":{"format":"mp3","stereo":false,"direction":"listen"}}}');

    $this->action->update($msg);

    $this->assertTrue($this->action->finished);
    $this->assertEquals($this->action->state, 'finished');
    $this->assertInstanceOf('SignalWire\Relay\Calling\RecordResult', $this->action->result);
  }
}
