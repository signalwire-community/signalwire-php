<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use SignalWire\Messages\Execute;

class RelayCallingCallTest extends RelayCallingBaseActionCase
{
  protected $stub;

  protected function setUp() {
    parent::setUp();
    $this->_successResponse = \React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message","control_id":"' . self::UUID . '"}}'));
    $this->_failResponse = \React\Promise\reject(json_decode('{"result":{"code":"400","message":"some error","control_id":"' . self::UUID . '"}}'));

    $this->stateNotificationCreated = json_decode( '{"event_type":"calling.call.state","params":{"call_state":"created","direction":"inbound","device":{"type":"phone","params":{"from_number":"+1234","to_number":"15678"}},"tag":"'.self::UUID.'","call_id":"call-id","node_id":"node-id"}}');
    $this->stateNotificationAnswered = json_decode('{"event_type":"calling.call.state","params":{"call_state":"answered","direction":"inbound","device":{"type":"phone","params":{"from_number":"+1234","to_number":"15678"}},"call_id":"call-id","node_id":"node-id"}}');
    $this->stateNotificationEnded = json_decode('{"event_type":"calling.call.state","params":{"call_state":"ended","end_reason":"busy","direction":"inbound","device":{"type":"phone","params":{"from_number":"+1234","to_number":"15678"}},"call_id":"call-id","node_id":"node-id"}}');
    $this->recordNotification = json_decode('{"event_type":"calling.call.record","params":{"state":"finished","record":{"audio":{"format":"mp3","direction":"speak","stereo":false}},"url":"record.mp3","control_id":"'.self::UUID.'","size":4096,"duration":4,"call_id":"call-id","node_id":"node-id"}}');
    $this->playNotification = json_decode('{"event_type":"calling.call.play","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","state":"finished"}}');
    $this->collectNotification = json_decode('{"event_type":"calling.call.collect","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","result":{"type":"digit","params":{"digits":"12345","terminator":"#"}}}}');
    $this->connectNotification = json_decode('{"event_type":"calling.call.connect","params":{"connect_state":"connected","peer":{"call_id":"peer-call-id","node_id":"peer-node-id","device":{"type":"phone","params":{"from_number":"+1234","to_number":"+15678"}}},"call_id":"call-id","node_id":"node-id"}}');
    $this->connectNotificationPeerCreated = json_decode('{"event_type":"calling.call.state","params":{"call_state":"created","direction":"outbound","device":{"type":"phone","params":{"from_number":"+1234","to_number":"15678"}},"peer":{"call_id":"call-id","node_id":"node-id"},"call_id":"peer-call-id","node_id":"peer-node-id"}}');
    $this->connectNotificationFailed = json_decode('{"event_type":"calling.call.connect","params":{"connect_state":"failed","peer":{"call_id":"peer-call-id","node_id":"peer-node-id"},"call_id":"call-id","node_id":"node-id"}}');
  }

  public function testDialSuccess(): void {
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->dial()->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DialResult', $result);
      $this->assertTrue($result->isSuccessful());
      $this->assertEquals($result->getCall(), $this->call);
    });

    $this->calling->notificationHandler($this->stateNotificationCreated);
    $this->calling->notificationHandler($this->stateNotificationAnswered);
  }

  public function testDialFail(): void {
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_failResponse);

    $this->call->dial()->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DialResult', $result);
      $this->assertFalse($result->isSuccessful());
      $this->assertEquals($result->getEvent(), null);
    });

    $this->calling->notificationHandler($this->stateNotificationCreated);
    $this->calling->notificationHandler($this->stateNotificationAnswered);
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->hangup()->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\HangupResult', $result);
      $this->assertEquals($result->getReason(), 'busy');
      $this->assertEquals($result->getEvent()->payload->direction, 'inbound');
    });
    $this->calling->notificationHandler($this->stateNotificationEnded);
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_failResponse);

    $this->call->hangup()->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\HangupResult', $result);
      $this->assertFalse($result->isSuccessful());
      $this->assertEquals($result->getEvent(), null);
    });
    $this->calling->notificationHandler($this->stateNotificationEnded);
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->answer()->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\AnswerResult', $result);
      $this->assertTrue($result->isSuccessful());
      $this->assertEquals($result->getEvent()->payload->direction, 'inbound');
    });
    $this->calling->notificationHandler($this->stateNotificationAnswered);
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_failResponse);

    $this->call->answer()->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\AnswerResult', $result);
      $this->assertFalse($result->isSuccessful());
      $this->assertEquals($result->getEvent(), null);
    });
    $this->calling->notificationHandler($this->stateNotificationAnswered);
  }

  public function testRecordSuccess(): void {
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $record = ["beep" => true, "stereo" => false];
    $this->call->record($record)->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\RecordResult', $result);
      $this->assertTrue($result->isSuccessful());
      $this->assertEquals($result->getUrl(), 'record.mp3');
      $this->assertObjectHasAttribute('url', $result->getEvent()->payload);
    });

    $this->calling->notificationHandler($this->recordNotification);
  }

  public function testRecordFail(): void {
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_failResponse);

    $record = ["beep" => true, "stereo" => false];
    $this->call->record($record)->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\RecordResult', $result);
      $this->assertFalse($result->isSuccessful());
    });

    $this->calling->notificationHandler($this->recordNotification);
  }

  public function testRecordAsyncSuccess(): void {
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $record = ["beep" => true, "stereo" => false];
    $this->call->recordAsync($record)->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\RecordAction', $action);
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\RecordResult', $action->getResult());
      $this->assertFalse($action->isCompleted());

      $this->calling->notificationHandler($this->recordNotification);

      $this->assertTrue($action->isCompleted());
    });
  }

  public function testRecordAsyncFail(): void {
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_failResponse);

    $record = ["beep" => true, "stereo" => false];
    $this->call->recordAsync($record)->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\RecordAction', $action);
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\RecordResult', $action->getResult());
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getState(), 'failed');
    });
  }

  public function testPlaySuccess(): void {
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->play(
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    )->done([$this, '__syncPlayCheck']);
    $this->calling->notificationHandler($this->playNotification);
  }

  public function testPlayFail(): void {
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_failResponse);

    $this->call->play(
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    )->done(function ($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PlayResult', $result);
      $this->assertFalse($result->isSuccessful());
    });
    $this->calling->notificationHandler($this->playNotification);
  }

  public function testPlayAsyncSuccess(): void {
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->playAsync(
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    )->done([$this, '__asyncPlayCheck']);
  }

  public function testPlayAsyncFail(): void {
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_failResponse);

    $this->call->playAsync(
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    )->done(function ($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\PlayAction', $action);
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getState(), 'failed');
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->playAudio('url-to-audio.mp3')->done([$this, '__syncPlayCheck']);
    $this->calling->notificationHandler($this->playNotification);
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->playAudioAsync('url-to-audio.mp3')->done([$this, '__asyncPlayCheck']);
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->playSilence(5)->done([$this, '__syncPlayCheck']);
    $this->calling->notificationHandler($this->playNotification);
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->playSilenceAsync(5)->done([$this, '__asyncPlayCheck']);
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->playTTS(['text' => 'Welcome', 'gender' => 'male'])->done([$this, '__syncPlayCheck']);
    $this->calling->notificationHandler($this->playNotification);
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->playTTSAsync(['text' => 'Welcome', 'gender' => 'male'])->done([$this, '__asyncPlayCheck']);
  }

  public function testPromptSuccess(): void {
    $this->_setCallReady();

    $collect = ["initial_timeout" => 10, "digits" => [ "max" => 3 ]];
    $play = [
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    ];
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play_and_collect',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'collect' => $collect,
        'play' => $play
      ]
    ]);

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->prompt($collect, ...$play)->done([$this, '__syncPromptCheck']);

    $this->calling->notificationHandler($this->collectNotification);
  }

  public function testPromptFail(): void {
    $this->_setCallReady();

    $collect = ["initial_timeout" => 10, "digits" => [ "max" => 3 ]];
    $play = [
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    ];
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play_and_collect',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'collect' => $collect,
        'play' => $play
      ]
    ]);

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_failResponse);

    $this->call->prompt($collect, ...$play)->done(function ($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PromptResult', $result);
      $this->assertFalse($result->isSuccessful());
    });

    $this->calling->notificationHandler($this->collectNotification);
  }

  public function testPromptAsyncSuccess(): void {
    $this->_setCallReady();

    $collect = ["initial_timeout" => 10, "digits" => [ "max" => 3 ]];
    $play = [
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    ];
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play_and_collect',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'collect' => $collect,
        'play' => $play
      ]
    ]);

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->promptAsync($collect, ...$play)->done([$this, '__asyncPromptCheck']);
  }

  public function testPromptAsyncFail(): void {
    $this->_setCallReady();

    $collect = ["initial_timeout" => 10, "digits" => [ "max" => 3 ]];
    $play = [
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    ];
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play_and_collect',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'collect' => $collect,
        'play' => $play
      ]
    ]);

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_failResponse);

    $this->call->promptAsync($collect, ...$play)->done(function ($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\PromptAction', $action);
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getState(), 'failed');
    });
  }

  public function testPromptTTS(): void {
    $this->_setCallReady();

    $collect = ["initial_timeout" => 10, "digits" => ["max" => 3]];
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->promptTTS($collect, ['text' => 'Welcome', 'gender' => 'male'])->done([$this, '__syncPromptCheck']);
    $this->calling->notificationHandler($this->collectNotification);
  }

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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->promptTTSAsync($collect, ['text' => 'Welcome', 'gender' => 'male'])->done([$this, '__asyncPromptCheck']);
  }

  public function testPromptAudio(): void {
    $this->_setCallReady();

    $collect = ["initial_timeout" => 10, "digits" => ["max" => 3]];
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play_and_collect',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'collect' => $collect,
        'play' => [
          ['type' => 'audio', 'params' => ['url' => 'audio.mp3']]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->promptAudio($collect, 'audio.mp3')->done([$this, '__syncPromptCheck']);
    $this->calling->notificationHandler($this->collectNotification);
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
          ['type' => 'audio', 'params' => ['url' => 'audio.mp3']]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->promptAudioAsync($collect, 'audio.mp3')->done([$this, '__asyncPromptCheck']);
  }

  public function testConnectSyncFail(): void {
    $this->_setCallReady();

    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.connect',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'devices' => [
          [
            [ "type" => "phone", "params" => [ "to_number" => "999", "from_number" => "231", "timeout" => 10 ] ]
          ],
          [
            [ "type" => "phone", "params" => [ "to_number" => "888", "from_number" => "234", "timeout" => 20 ] ]
          ]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_failResponse);

    $this->call->connect(
      [ "type" => "phone", "to" => "999", "from" => "231", "timeout" => 10 ],
      [ "type" => "phone", "to" => "888" ]
    )->done(function ($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\ConnectResult', $result);
      $this->assertFalse($result->isSuccessful());
    });

    $this->calling->notificationHandler($this->connectNotificationPeerCreated);
    $this->calling->notificationHandler($this->connectNotification);
  }

  public function testConnectAsyncFail(): void {
    $this->_setCallReady();

    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.connect',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'devices' => [
          [
            [ "type" => "phone", "params" => [ "to_number" => "999", "from_number" => "231", "timeout" => 10 ] ]
          ],
          [
            [ "type" => "phone", "params" => [ "to_number" => "888", "from_number" => "234", "timeout" => 20 ] ]
          ]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_failResponse);

    $this->call->connectAsync(
      [ "type" => "phone", "to" => "999", "from" => "231", "timeout" => 10 ],
      [ "type" => "phone", "to" => "888" ]
    )->done(function ($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\ConnectAction', $action);
      $this->assertTrue($action->isCompleted());
    });

    $this->calling->notificationHandler($this->connectNotificationPeerCreated);
    $this->calling->notificationHandler($this->connectNotification);
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
            [ "type" => "phone", "params" => [ "to_number" => "999", "from_number" => "231", "timeout" => 10 ] ]
          ],
          [
            [ "type" => "phone", "params" => [ "to_number" => "888", "from_number" => "234", "timeout" => 20 ] ]
          ]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->connect(
      [ "type" => "phone", "to" => "999", "from" => "231", "timeout" => 10 ],
      [ "type" => "phone", "to" => "888" ]
    )->done([$this, '__syncConnectCheck']);

    $this->calling->notificationHandler($this->connectNotificationPeerCreated);
    $this->calling->notificationHandler($this->connectNotification);
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->connect(
      [
        [ "type" => "phone", "to" => "999", "from" => "231", "timeout" => 10 ],
        [ "type" => "phone", "to" => "888" ]
      ]
    )->done([$this, '__syncConnectCheck']);

    $this->calling->notificationHandler($this->connectNotificationPeerCreated);
    $this->calling->notificationHandler($this->connectNotification);
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->connect(
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
    )->done([$this, '__syncConnectCheck']);

    $this->calling->notificationHandler($this->connectNotificationPeerCreated);
    $this->calling->notificationHandler($this->connectNotification);
  }

  public function testConnectAsyncDevicesInSeries(): void {
    $this->_setCallReady();

    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.connect',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'devices' => [
          [
            [ "type" => "phone", "params" => [ "to_number" => "999", "from_number" => "231", "timeout" => 10 ] ]
          ],
          [
            [ "type" => "phone", "params" => [ "to_number" => "888", "from_number" => "234", "timeout" => 20 ] ]
          ]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->connectAsync(
      [ "type" => "phone", "to" => "999", "from" => "231", "timeout" => 10 ],
      [ "type" => "phone", "to" => "888" ]
    )->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\ConnectAction', $action);
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\ConnectResult', $action->getResult());
      $this->assertFalse($action->isCompleted());

      $this->calling->notificationHandler($this->connectNotification);

      $this->assertEquals($action->getState(), 'connected');
      $this->assertTrue($action->isCompleted());
    });
  }

  public function testConnectAsyncDevicesInSeriesWithFailure(): void {
    $this->_setCallReady();

    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.connect',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'devices' => [
          [
            [ "type" => "phone", "params" => [ "to_number" => "999", "from_number" => "231", "timeout" => 10 ] ]
          ],
          [
            [ "type" => "phone", "params" => [ "to_number" => "888", "from_number" => "234", "timeout" => 20 ] ]
          ]
        ]
      ]
    ]);

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->connectAsync(
      [ "type" => "phone", "to" => "999", "from" => "231", "timeout" => 10 ],
      [ "type" => "phone", "to" => "888" ]
    )->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\ConnectAction', $action);
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\ConnectResult', $action->getResult());
      $this->assertFalse($action->isCompleted());

      $this->calling->notificationHandler($this->connectNotificationFailed);

      $this->assertEquals($action->getState(), 'failed');
      $this->assertTrue($action->isCompleted());
      $this->assertFalse($action->getResult()->isSuccessful());
    });
  }

  public function testWaitForAnswered(): void {
    $this->call->waitFor('answered')->done(function($check) {
      $this->assertTrue($check);
    });

    $this->calling->notificationHandler($this->stateNotificationCreated);
    $this->calling->notificationHandler($this->stateNotificationAnswered);
  }

  public function testWaitForAnsweredAlreadyDone(): void {
    $this->call->state = 'answered';

    $this->call->waitFor('ringing', 'answered')->done(function($check) {
      $this->assertTrue($check);
    });
  }

  public function testWaitForEnded(): void {
    $this->call->waitFor('ending', 'ended')->done(function($check) {
      $this->assertTrue($check);
    });

    $this->calling->notificationHandler($this->stateNotificationCreated);
    $this->calling->notificationHandler($this->stateNotificationEnded);
  }

  public function testWaitForUnansweredCall(): void {
    $this->call->waitFor('answered')->done(function($check) {
      $this->assertFalse($check);
    });

    $this->calling->notificationHandler($this->stateNotificationCreated);
    $this->calling->notificationHandler($this->stateNotificationEnded);
  }

  /**
   * Callable to not repeat the same function for every SYNC play test
   */
  public function __syncPlayCheck($result) {
    $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PlayResult', $result);
    $this->assertTrue($result->isSuccessful());
    $this->assertObjectHasAttribute('state', $result->getEvent()->payload);
  }

  /**
   * Callable to not repeat the same function for every ASYNC play test
  */
  public function __asyncPlayCheck($action) {
    $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\PlayAction', $action);
    $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PlayResult', $action->getResult());
    $this->assertFalse($action->isCompleted());

    $this->calling->notificationHandler($this->playNotification);

    $this->assertTrue($action->isCompleted());
  }

  /**
   * Callable to not repeat the same function for every SYNC prompt test
   */
  public function __syncPromptCheck($result) {
    $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PromptResult', $result);
    $this->assertTrue($result->isSuccessful());
    $this->assertEquals($result->getType(), 'digit');
    $this->assertEquals($result->getTerminator(), '#');
    $this->assertObjectHasAttribute('type', $result->getEvent()->payload);
    $this->assertObjectHasAttribute('params', $result->getEvent()->payload);
  }

  /**
   * Callable to not repeat the same function for every ASYNC prompt test
  */
  public function __asyncPromptCheck($action) {
    $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\PromptAction', $action);
    $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PromptResult', $action->getResult());
    $this->assertFalse($action->isCompleted());

    $this->calling->notificationHandler($this->collectNotification);

    $this->assertTrue($action->isCompleted());
  }

  /**
   * Callable to not repeat the same function for every SYNC connect test
   */
  public function __syncConnectCheck($result) {
    $this->assertInstanceOf('SignalWire\Relay\Calling\Results\ConnectResult', $result);
    $this->assertTrue($result->isSuccessful());
    $this->assertEquals($result->getCall(), $this->call->peer);
    $this->assertEquals($result->getCall()->id, 'peer-call-id');
    $this->assertObjectHasAttribute('peer', $result->getEvent()->payload);
    $this->assertObjectHasAttribute('connect_state', $result->getEvent()->payload);
  }
}
