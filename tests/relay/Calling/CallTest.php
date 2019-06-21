<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Client;
use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\Notification;
use SignalWire\Messages\Execute;

class RelayCallingCallTest extends RelayCallingBaseActionCase
{
  protected $stub;

  protected function setUp() {
    parent::setUp();
    $this->stateNotificationAnswered = json_decode('{"call_state":"answered","call_id":"call-id","event_type":"'.Notification::State.'"}');
    $this->stateNotificationEnded = json_decode('{"call_state":"ended","call_id":"call-id","event_type":"'.Notification::State.'"}');
    $this->playNotification = json_decode('{"state":"finished","call_id":"call-id","control_id":"'.self::UUID.'","event_type":"'.Notification::Play.'"}');
    $this->collectNotification = json_decode('{"control_id":"'.self::UUID.'","call_id":"call-id","event_type":"'.Notification::Collect.'","result":{"type":"digit","params":{"digits":"12345","terminator":"#"}}}');
    $this->recordNotification = json_decode('{"state":"finished","call_id":"call-id","control_id":"'.self::UUID.'","event_type":"'.Notification::Record.'","url":"record-url","record":{"audio":{"type":"digit","params":{"digits":"12345","terminator":"#"}}}}');
    $this->connectNotification = json_decode('{"connect_state":"connected","call_id":"call-id","event_type":"'.Notification::Connect.'"}');
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

  public function testHangupSuccess(): void {
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

    $this->call->hangup()->done(function($call) {
      $this->assertEquals($call->state, 'ended');
      $this->assertInstanceOf('SignalWire\Relay\Calling\Call', $call);
    });
    $this->call->_stateChange($this->stateNotificationEnded);
  }

  public function testHangupFail(): void {
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
      ->willReturn(\React\Promise\reject(json_decode('{"result":{"code":"400","message":"some error"}}')));

    $this->expectException(Exception::class);
    $this->call->hangup()->done();
  }

  public function testAnswerSuccess(): void {
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

    $this->call->answer()->done(function($call) {
      $this->assertEquals($call->state, 'answered');
      $this->assertInstanceOf('SignalWire\Relay\Calling\Call', $call);
    });
    $this->call->_stateChange($this->stateNotificationAnswered);
  }

  public function testAnswerFail(): void {
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
      ->willReturn(\React\Promise\reject(json_decode('{"result":{"code":"400","message":"some error"}}')));

    $this->expectException(Exception::class);
    $this->call->answer()->done();
  }

  public function testPlayAudioAsync(): void {
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

    $this->call->playAudioAsync('url-to-audio.mp3')->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PlayAction', $action);
    });
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

    $this->call->playAudio('url-to-audio.mp3')->done(function($res) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PlayResult', $res);
      $this->assertFalse($res->failed);
      $this->assertTrue($res->succeeded);
      $this->assertEquals($res->state, 'finished');
    });
    $this->call->_playStateChange($this->playNotification);
  }

  public function testPlaySilenceAsync(): void {
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

    $this->call->playSilenceAsync(5)->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PlayAction', $action);
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

    $this->call->playSilence(5)->done(function($res) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PlayResult', $res);
      $this->assertFalse($res->failed);
      $this->assertTrue($res->succeeded);
      $this->assertEquals($res->state, 'finished');
    });
    $this->call->_playStateChange($this->playNotification);
  }

  public function testPlayTTSAsync(): void {
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

    $this->call->playTTSAsync(['text' => 'Welcome', 'gender' => 'male'])->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PlayAction', $action);
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

    $this->call->playTTS(['text' => 'Welcome', 'gender' => 'male'])->done(function($res) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PlayResult', $res);
      $this->assertFalse($res->failed);
      $this->assertTrue($res->succeeded);
      $this->assertEquals($res->state, 'finished');
    });
    $this->call->_playStateChange($this->playNotification);
  }

  public function testPlayAsync(): void {
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

    $this->call->playAsync(
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    )->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PlayAction', $action);
    });
  }

  public function testPlay(): void {
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

    $this->call->play(
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    )->done(function($res) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PlayResult', $res);
      $this->assertFalse($res->failed);
      $this->assertTrue($res->succeeded);
      $this->assertEquals($res->state, 'finished');
    });
    $this->call->_playStateChange($this->playNotification);
  }

  public function testRecordAsync(): void {
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
    $this->call->recordAsync($record)->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\RecordAction', $action);
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
      ->willReturn(\React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message","control_id":"'.self::UUID.'"}}')));

    $record = ["beep" => true, "stereo" => false];
    $this->call->record($record)->done(function($params) {
      $this->assertEquals($params->url, 'record-url');
    });

    $this->call->_recordStateChange($this->recordNotification);
  }

  public function testPromptAudioAsync(): void {
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

    $this->call->promptAudioAsync($collect, 'url-to-audio.mp3')->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PromptAction', $action);
    });
  }

  public function testPromptAudio(): void {
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

    $this->call->promptAudio($collect, 'url-to-audio.mp3')->done(function($result) {
      $this->assertEquals($result->type, 'digit');
    });
    $this->call->_collectStateChange($this->collectNotification);
  }
/*
  public function testPlaySilenceAndCollectAsync(): void {
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

    $this->call->playSilenceAndCollectAsync($collect, 5)->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PromptAction', $action);
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

    $this->call->playSilenceAndCollect($collect, 5)->done(function($result) {
      $this->assertEquals($result->type, 'digit');
    });
    $this->call->_collectStateChange($this->collectNotification);
  }
*/
  public function testPromptTTSAsync(): void {
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

    $this->call->promptTTSAsync($collect, ['text' => 'Welcome', 'gender' => 'male'])->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PromptAction', $action);
    });
  }

  public function testPromptTTS(): void {
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

    $this->call->promptTTS($collect, ['text' => 'Welcome', 'gender' => 'male'])->done(function($result) {
      $this->assertEquals($result->type, 'digit');
    });
    $this->call->_collectStateChange($this->collectNotification);
  }

  public function testPromptAsync(): void {
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

    $this->call->promptAsync(
      $collect,
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    )->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\PromptAction', $action);
    });
  }

  public function testPrompt(): void {
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

    $this->call->prompt(
      $collect,
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    )->done(function($result) {
      $this->assertEquals($result->type, 'digit');
    });
    $this->call->_collectStateChange($this->collectNotification);
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
    )->done(function($call) {
      $this->assertEquals($call->connectState, 'connected');
      $this->assertInstanceOf('SignalWire\Relay\Calling\Call', $call);
    });
    $this->call->_connectStateChange($this->connectNotification);
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
    )->done(function($call) {
      $this->assertEquals($call->connectState, 'connected');
      $this->assertInstanceOf('SignalWire\Relay\Calling\Call', $call);
    });
    $this->call->_connectStateChange($this->connectNotification);
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
    )->done(function($call) {
      $this->assertEquals($call->connectState, 'connected');
      $this->assertInstanceOf('SignalWire\Relay\Calling\Call', $call);
    });
    $this->call->_connectStateChange($this->connectNotification);
  }
}
