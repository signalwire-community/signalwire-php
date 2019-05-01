<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Calling\PlayMediaAndCollectAction;
use SignalWire\Relay\Calling\PlayAudioAndCollectAction;
use SignalWire\Relay\Calling\PlaySilenceAndCollectAction;
use SignalWire\Relay\Calling\PlayTTSAndCollectAction;
use SignalWire\Messages\Execute;

class RelayCallingPlayAndCollectActionTest extends RelayCallingBaseActionCase
{
  protected function setUp() {
    parent::setUp();
    $this->_setCallReady();
  }

  public function testPlayMediaAndCollectActionStop(): void {
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play_and_collect.stop',
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

    $action = new PlayMediaAndCollectAction($this->call, 'control-id');
    $res = $action->stop();
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }

  public function testPlayAudioAndCollectActionStop(): void {
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play_and_collect.stop',
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

    $action = new PlayAudioAndCollectAction($this->call, 'control-id');
    $res = $action->stop();
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }

  public function testPlaySilenceAndCollectActionStop(): void {
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play_and_collect.stop',
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

    $action = new PlaySilenceAndCollectAction($this->call, 'control-id');
    $res = $action->stop();
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }

  public function testPlayTTSAndCollectActionStop(): void {
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play_and_collect.stop',
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

    $action = new PlayTTSAndCollectAction($this->call, 'control-id');
    $res = $action->stop();
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }
}
