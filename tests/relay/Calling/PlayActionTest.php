<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Calling\PlayMediaAction;
use SignalWire\Relay\Calling\PlayAudioAction;
use SignalWire\Relay\Calling\PlaySilenceAction;
use SignalWire\Relay\Calling\PlayTTSAction;
use SignalWire\Messages\Execute;

class RelayCallingPlayActionTest extends RelayCallingBaseActionCase
{
  public function testPlayMediaActionStop(): void {
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play.stop',
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

    $action = new PlayMediaAction($this->call, 'control-id');
    $res = $action->stop();
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }

  public function testPlayAudioActionStop(): void {
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play.stop',
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

    $action = new PlayAudioAction($this->call, 'control-id');
    $res = $action->stop();
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }

  public function testPlaySilenceActionStop(): void {
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play.stop',
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

    $action = new PlaySilenceAction($this->call, 'control-id');
    $res = $action->stop();
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }

  public function testPlayTTSActionStop(): void {
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play.stop',
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

    $action = new PlayTTSAction($this->call, 'control-id');
    $res = $action->stop();
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }
}
