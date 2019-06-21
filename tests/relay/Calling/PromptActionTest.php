<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Calling\PromptAction;
use SignalWire\Messages\Execute;

class RelayCallingPromptActionTest extends RelayCallingBaseActionCase
{
  protected function setUp() {
    parent::setUp();
    $this->_setCallReady();
    $this->action = new PromptAction($this->call);
  }

  public function testPlayMediaAndCollectActionStop(): void {
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play_and_collect.stop',
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

    $res = $this->action->stop()->done(function($result) {
      $this->assertEquals($result->code, '200');
    });
  }

  public function testUpdateWithError(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","result":{"type":"error"}}');

    $this->action->update($msg);

    $this->assertTrue($this->action->finished);
    $this->assertEquals($this->action->state, 'error');
    $this->assertInstanceOf('SignalWire\Relay\Calling\PromptResult', $this->action->result);
  }

  public function testUpdateWithNoInput(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","result":{"type":"no_input"}}');

    $this->action->update($msg);

    $this->assertTrue($this->action->finished);
    $this->assertEquals($this->action->state, 'no_input');
    $this->assertInstanceOf('SignalWire\Relay\Calling\PromptResult', $this->action->result);
  }

  public function testUpdateWithNoMatch(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","result":{"type":"no_match"}}');

    $this->action->update($msg);

    $this->assertTrue($this->action->finished);
    $this->assertEquals($this->action->state, 'no_match');
    $this->assertInstanceOf('SignalWire\Relay\Calling\PromptResult', $this->action->result);
  }

  public function testUpdateWithDigits(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","result":{"type":"digit","params":{"digits":"12345","terminator":"#"}}}');

    $this->action->update($msg);

    $this->assertTrue($this->action->finished);
    $this->assertEquals($this->action->state, 'successful');
    $this->assertInstanceOf('SignalWire\Relay\Calling\PromptResult', $this->action->result);
  }

  public function testUpdateWithSpeech(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","result":{"type":"speech","params":{"text":"utterance heard","confidence":83.2}}}');

    $this->action->update($msg);

    $this->assertTrue($this->action->finished);
    $this->assertEquals($this->action->state, 'successful');
    $this->assertInstanceOf('SignalWire\Relay\Calling\PromptResult', $this->action->result);
  }
}
