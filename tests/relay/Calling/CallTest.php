<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Client;
use SignalWire\Relay\Calling\Call;
use SignalWire\Messages\Execute;

class RelayCallingCallTest extends RelayCallingBaseActionCase
{
  protected $stub;

  protected function setUp() {
    parent::setUp();
  }

  public function testBegin(): void {
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.begin',
      'params' => [
        'tag' => self::UUID,
        'device' => [
          'type' => 'phone',
          'params' => ['from_number' => '234', 'to_number' => '456', 'timeout' => 20]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())
      ->method('send')
      ->with($msg)
      ->willReturn(\React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message"}}')));

    $res = $this->call->begin();
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }

  public function testHangup(): void {
    $this->_setCallReady();
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.end',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'reason' => 'hangup'
      ]
    ]);

    $this->client->connection->expects($this->once())
      ->method('send')
      ->with($msg)
      ->willReturn(\React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message"}}')));

    $res = $this->call->hangup();
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }

  public function testAnswer(): void {
    $this->_setCallReady();
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.answer',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id'
      ]
    ]);

    $this->client->connection->expects($this->once())
      ->method('send')
      ->with($msg)
      ->willReturn(\React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message"}}')));

    $res = $this->call->answer();
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }

  public function testPlayAudio(): void {
    $this->_setCallReady();
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'play' => [
          ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())
      ->method('send')
      ->with($msg)
      ->willReturn(\React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message","control_id":"control-id"}}')));

    $this->call->playAudio('url-to-audio.mp3')->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PlayAudioAction', $action);
    });
  }

  public function testPlaySilence(): void {
    $this->_setCallReady();
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'play' => [
          ['type' => 'silence', 'params' => ['duration' => 5]]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())
      ->method('send')
      ->with($msg)
      ->willReturn(\React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message","control_id":"control-id"}}')));

    $this->call->playSilence(5)->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PlaySilenceAction', $action);
    });
  }

  public function testPlayTTS(): void {
    $this->_setCallReady();
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'play' => [
          ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())
      ->method('send')
      ->with($msg)
      ->willReturn(\React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message","control_id":"control-id"}}')));

    $this->call->playTTS(['text' => 'Welcome', 'gender' => 'male'])->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PlayTTSAction', $action);
    });
  }

  public function testPlayMedia(): void {
    $this->_setCallReady();
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'play' => [
          ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
          ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
          ['type' => 'silence', 'params' => ['duration' => 5]]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())
      ->method('send')
      ->with($msg)
      ->willReturn(\React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message","control_id":"control-id"}}')));

    $this->call->playMedia(
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    )->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PlayMediaAction', $action);
    });
  }

  public function testRecord(): void {
    $this->_setCallReady();
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.record',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'record' => ["beep" => true, "stereo" => false]
      ]
    ]);

    $this->client->connection->expects($this->once())
      ->method('send')
      ->with($msg)
      ->willReturn(\React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message","control_id":"control-id"}}')));

    $record = ["beep" => true, "stereo" => false];
    $this->call->record($record)->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\RecordAction', $action);
    });
  }

  public function testPlayAudioAndCollect(): void {
    $this->_setCallReady();
    $collect = ["initial_timeout" => 10, "digits" => [ "max" => 3 ]];
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play_and_collect',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'collect' => $collect,
        'play' => [
          ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())
      ->method('send')
      ->with($msg)
      ->willReturn(\React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message","control_id":"control-id"}}')));

    $this->call->playAudioAndCollect($collect, 'url-to-audio.mp3')->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PlayAudioAndCollectAction', $action);
    });
  }

  public function testPlaySilenceAndCollect(): void {
    $this->_setCallReady();
    $collect = ["initial_timeout" => 10, "digits" => [ "max" => 3 ]];
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play_and_collect',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'collect' => $collect,
        'play' => [
          ['type' => 'silence', 'params' => ['duration' => 5]]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())
      ->method('send')
      ->with($msg)
      ->willReturn(\React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message","control_id":"control-id"}}')));

    $this->call->playSilenceAndCollect($collect, 5)->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PlaySilenceAndCollectAction', $action);
    });
  }

  public function testPlayTTSAndCollect(): void {
    $this->_setCallReady();
    $collect = ["initial_timeout" => 10, "digits" => [ "max" => 3 ]];
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play_and_collect',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'collect' => $collect,
        'play' => [
          ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())
      ->method('send')
      ->with($msg)
      ->willReturn(\React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message","control_id":"control-id"}}')));

    $this->call->playTTSAndCollect($collect, ['text' => 'Welcome', 'gender' => 'male'])->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PlayTTSAndCollectAction', $action);
    });
  }

  public function testPlayMediaAndCollect(): void {
    $this->_setCallReady();
    $collect = ["initial_timeout" => 10, "digits" => [ "max" => 3 ]];
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play_and_collect',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'collect' => $collect,
        'play' => [
          ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
          ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
          ['type' => 'silence', 'params' => ['duration' => 5]]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())
      ->method('send')
      ->with($msg)
      ->willReturn(\React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message","control_id":"control-id"}}')));

    $this->call->playMediaAndCollect(
      $collect,
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    )->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PlayMediaAndCollectAction', $action);
    });
  }

  public function testConnectDevicesInSeries(): void {
    $this->_setCallReady();
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.connect',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'devices' => [
          [
            [ "type" => "phone", "params" => [ "to_number" => "999", "from_number" => "231", "timeout" => 10 ] ],
          ],
          [
            [ "type" => "phone", "params" => [ "to_number" => "888", "from_number" => "234", "timeout" => 20 ] ]
          ]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())
      ->method('send')
      ->with($msg)
      ->willReturn(\React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message"}}')));

    $res = $this->call->connect(
      [ "type" => "phone", "to" => "999", "from" => "231", "timeout" => 10 ],
      [ "type" => "phone", "to" => "888" ]
    );
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }

  public function testConnectDevicesInParallel(): void {
    $this->_setCallReady();
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.connect',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'devices' => [
          [
            [ "type" => "phone", "params" => [ "to_number" => "999", "from_number" => "231", "timeout" => 10 ] ],
            [ "type" => "phone", "params" => [ "to_number" => "888", "from_number" => "234", "timeout" => 20 ] ]
          ]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())
      ->method('send')
      ->with($msg)
      ->willReturn(\React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message"}}')));

    $res = $this->call->connect(
      [
        [ "type" => "phone", "to" => "999", "from" => "231", "timeout" => 10 ],
        [ "type" => "phone", "to" => "888" ]
      ]
    );
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }

  public function testConnectDevicesInBothSeriesAndParallel(): void {
    $this->_setCallReady();
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.connect',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'devices' => [
          [
            [ "type" => "phone", "params" => [ "to_number" => "999", "from_number" => "234", "timeout" => 20 ] ]
          ],
          [
            [ "type" => "phone", "params" => [ "to_number" => "555", "from_number" => "234", "timeout" => 20 ] ]
          ],
          [
            [ "type" => "phone", "params" => [ "to_number" => "999", "from_number" => "231", "timeout" => 10 ] ],
            [ "type" => "phone", "params" => [ "to_number" => "888", "from_number" => "234", "timeout" => 20 ] ]
          ]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())
      ->method('send')
      ->with($msg)
      ->willReturn(\React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message"}}')));

    $res = $this->call->connect(
      [
        [ "type" => "phone", "to" => "999" ],
      ],
      [
        [ "type" => "phone", "to" => "555" ],
      ],
      [
        [ "type" => "phone", "to" => "999", "from" => "231", "timeout" => 10 ],
        [ "type" => "phone", "to" => "888" ]
      ]
    );
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }
}
