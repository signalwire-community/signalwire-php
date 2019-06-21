<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Calling\PlayAction;
use SignalWire\Messages\Execute;

class RelayCallingPlayActionTest extends RelayCallingBaseActionCase
{
  protected function setUp() {
    parent::setUp();
    $this->_setCallReady();
    $this->action = new PlayAction($this->call);
  }

  public function testPlayActionStop(): void {
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play.stop',
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

  public function testUpdateWithPlaying(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","state":"playing"}');

    $this->action->update($msg);

    $this->assertFalse($this->action->finished);
    $this->assertEquals($this->action->state, 'playing');
    $this->assertNull($this->action->result);
  }

  public function testUpdateWithNoInput(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","state":"error"}');

    $this->action->update($msg);

    $this->assertTrue($this->action->finished);
    $this->assertEquals($this->action->state, 'error');
    $this->assertInstanceOf('SignalWire\Relay\Calling\PlayResult', $this->action->result);
  }

  public function testUpdateWithFinished(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","state":"finished"}');

    $this->action->update($msg);

    $this->assertTrue($this->action->finished);
    $this->assertEquals($this->action->state, 'finished');
    $this->assertInstanceOf('SignalWire\Relay\Calling\PlayResult', $this->action->result);
  }
}
