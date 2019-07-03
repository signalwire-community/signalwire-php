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
    $this->_successResponse = \React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message","control_id":"' . self::UUID . '"}}'));
    // $this->_failResponse = \React\Promise\resolve(json_decode('{"result":{"code":"400","message":"message","control_id":"' . self::UUID . '"}}'));

    $this->stateNotificationCreated = json_decode( '{"event_type":"calling.call.state","params":{"call_state":"created","direction":"inbound","device":{"type":"phone","params":{"from_number":"+1234","to_number":"15678"}},"tag":"'.self::UUID.'","call_id":"call-id","node_id":"node-id"}}');
    $this->stateNotificationAnswered = json_decode('{"event_type":"calling.call.state","params":{"call_state":"answered","direction":"inbound","device":{"type":"phone","params":{"from_number":"+1234","to_number":"15678"}},"call_id":"call-id","node_id":"node-id"}}');
    $this->stateNotificationEnded = json_decode('{"event_type":"calling.call.state","params":{"call_state":"ended","end_reason":"busy","direction":"inbound","device":{"type":"phone","params":{"from_number":"+1234","to_number":"15678"}},"call_id":"call-id","node_id":"node-id"}}');
    $this->recordNotification = json_decode('{"event_type":"calling.call.record","params":{"state":"finished","record":{"audio":{"format":"mp3","direction":"speak","stereo":false}},"url":"record.mp3","control_id":"'.self::UUID.'","size":4096,"duration":4,"call_id":"call-id","node_id":"node-id"}}');
    $this->connectNotification = json_decode('{"event_type":"calling.call.connect","params":{"connect_state":"connected","device":{"node_id":"other-node-id","call_id":"other-call-id","tag":"other-tag-id","peer":{"type":"phone","params":{"from_number":"+1555","to_number":"+1777"}}},"tag":"'.self::UUID.'","call_id":"call-id","node_id":"node-id"}}');
    $this->playNotification = json_decode('{"event_type":"calling.call.play","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","state":"finished"}}');
    $this->collectNotification = json_decode('{"event_type":"calling.call.collect","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","result":{"type":"digit","params":{"digits":"12345","terminator":"#"}}}}');

    // $this->stateNotificationAnswered = json_decode('{"call_state":"answered","call_id":"call-id","event_type":"'.Notification::State.'"}');
    // $this->stateNotificationEnded = json_decode('{"call_state":"ended","reason":"busy","call_id":"call-id","event_type":"'.Notification::State.'"}');
    // $this->playNotification = json_decode('{"state":"finished","call_id":"call-id","control_id":"'.self::UUID.'","event_type":"'.Notification::Play.'"}');
    // $this->collectNotification = json_decode('{"control_id":"'.self::UUID.'","call_id":"call-id","event_type":"'.Notification::Collect.'","result":{"type":"digit","params":{"digits":"12345","terminator":"#"}}}');
    // $this->collectNotificationError = json_decode('{"control_id":"'.self::UUID.'","call_id":"call-id","event_type":"'.Notification::Collect.'","result":{"type":"error"}}');
    // $this->recordNotification = json_decode('{"state":"finished","call_id":"call-id","control_id":"'.self::UUID.'","event_type":"'.Notification::Record.'","url":"recording.mp3","record":{"audio":{"type":"digit","params":{"digits":"12345","terminator":"#"}}}}');
    // $this->connectNotification = json_decode('{"connect_state":"connected","call_id":"call-id","control_id":"'.self::UUID.'","event_type":"'.Notification::Connect.'"}');
    // $this->connectNotificationFailed = json_decode('{"connect_state":"failed","call_id":"call-id","control_id":"'.self::UUID.'","event_type":"'.Notification::Connect.'"}');
  }

  public function testDial(): void {
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
      $this->assertEquals($result->getEvent()->direction, 'inbound');
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
      $this->assertEquals($result->getEvent()->direction, 'inbound');
    });
    $this->calling->notificationHandler($this->stateNotificationAnswered);
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $record = ["beep" => true, "stereo" => false];
    $this->call->record($record)->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\RecordResult', $result);
      $this->assertTrue($result->isSuccessful());
      $this->assertEquals($result->getUrl(), 'record.mp3');
      $this->assertObjectHasAttribute('url', $result->getEvent());
    });

    $this->calling->notificationHandler($this->recordNotification);
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->play(
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    )->done([$this, '__syncPlayCheck']);
    $this->calling->notificationHandler($this->playNotification);
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

    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $this->call->playAsync(
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    )->done([$this, '__asyncPlayCheck']);
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

  /**
   * Callable to not repeat the same function for every SYNC play test
   */
  public function __syncPlayCheck($result) {
    $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PlayResult', $result);
    $this->assertTrue($result->isSuccessful());
    $this->assertObjectHasAttribute('state', $result->getEvent());
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
}
