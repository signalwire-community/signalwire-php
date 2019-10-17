<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use SignalWire\Messages\Execute;

class RelayCallingCallPromptTest extends RelayCallingBaseActionCase
{
  protected static $notificationFinished;
  public static $success;
  public static $fail;

  public static function setUpBeforeClass() {
    self::$notificationFinished = json_decode('{"event_type":"calling.call.collect","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","result":{"type":"digit","params":{"digits":"12345","terminator":"#"}}}}');
    self::$success = json_decode('{"result":{"code":"200","message":"message","control_id":"'.self::UUID.'"}}');
    self::$fail = json_decode('{"result":{"code":"400","message":"some error","control_id":"'.self::UUID.'"}}');
  }

  protected function setUp() {
    parent::setUp();

    $this->_setCallReady();
  }

  public function testPromptSuccess(): void {
    $collect = ['initial_timeout' => 10, 'digits' => [ 'max' => 3 ]];
    $play = [
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    ];
    $msg = $this->_promptMsg($collect, $play);
    $this->_mockSuccessResponse($msg, self::$success);

    $this->call->prompt($collect, ...$play)->done([$this, '__syncPromptCheck']);

    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testPromptSuccessWithVolume(): void {
    $collect = ['initial_timeout' => 10, 'digits' => [ 'max' => 3 ], 'volume' => 5.6];
    $play = [
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    ];
    $msg = $this->_promptMsg($collect, $play);
    $this->_mockSuccessResponse($msg, self::$success);

    $this->call->prompt($collect, ...$play)->done([$this, '__syncPromptCheck']);

    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testPromptSuccessWithFlattenedParameters(): void {
    $collect = ['initial_timeout' => 10, 'digits' => [ 'max' => 3 ], 'volume' => -4];
    $play = [
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    ];
    $msg = $this->_promptMsg($collect, $play);
    $this->_mockSuccessResponse($msg, self::$success);

    $params = [
      'initial_timeout' => 10,
      'digits_max' => 3,
      'volume' => -4,
      'media' => [
        ['type' => 'audio', 'url' => 'audio.mp3'],
        ['type' => 'tts', 'text' => 'Welcome', 'gender' => 'male'],
        ['type' => 'silence', 'duration' => 5]
      ]
    ];
    $this->call->prompt($params)->done([$this, '__syncPromptCheck']);

    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testPromptFail(): void {
    $collect = ['initial_timeout' => 10, 'digits' => [ 'max' => 3 ]];
    $play = [
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    ];
    $msg = $this->_promptMsg($collect, $play);
    $this->_mockFailResponse($msg, self::$fail);

    $this->call->prompt($collect, ...$play)->done(function ($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PromptResult', $result);
      $this->assertFalse($result->isSuccessful());
    });

    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testPromptAsyncSuccess(): void {
    $collect = ['initial_timeout' => 10, 'digits' => [ 'max' => 3 ]];
    $play = [
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    ];
    $msg = $this->_promptMsg($collect, $play);
    $this->_mockSuccessResponse($msg, self::$success);

    $this->call->promptAsync($collect, ...$play)->done([$this, '__asyncPromptCheck']);
  }

  public function testPromptAsyncFail(): void {
    $collect = ['initial_timeout' => 10, 'digits' => [ 'max' => 3 ]];
    $play = [
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    ];
    $msg = $this->_promptMsg($collect, $play);
    $this->_mockFailResponse($msg, self::$fail);

    $this->call->promptAsync($collect, ...$play)->done(function ($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\PromptAction', $action);
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getState(), 'failed');
    });
  }

  public function testPromptTTS(): void {
    $collect = ['initial_timeout' => 10, 'digits' => [ 'max' => 3 ]];
    $play = [
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']]
    ];
    $msg = $this->_promptMsg($collect, $play);
    $this->_mockSuccessResponse($msg, self::$success);

    $this->call->promptTTS($collect, ['text' => 'Welcome', 'gender' => 'male'])->done([$this, '__syncPromptCheck']);
    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testPromptTTSWithVolume(): void {
    $collect = ['initial_timeout' => 10, 'digits' => [ 'max' => 3 ], 'volume' => 5.4];
    $play = [
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']]
    ];
    $msg = $this->_promptMsg($collect, $play);
    $this->_mockSuccessResponse($msg, self::$success);

    $this->call->promptTTS(['initial_timeout' => 10, 'digits_max' => 3, 'volume' => 5.4], ['text' => 'Welcome', 'gender' => 'male'])->done([$this, '__syncPromptCheck']);
    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testPromptTTSAsync(): void {
    $collect = ['initial_timeout' => 10, 'digits' => [ 'max' => 3 ]];
    $play = [
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']]
    ];
    $msg = $this->_promptMsg($collect, $play);
    $this->_mockSuccessResponse($msg, self::$success);

    $this->call->promptTTSAsync($collect, ['text' => 'Welcome', 'gender' => 'male'])->done([$this, '__asyncPromptCheck']);
  }

  public function testPromptAudio(): void {
    $collect = ['initial_timeout' => 10, 'digits' => [ 'max' => 3 ]];
    $play = [
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']]
    ];
    $msg = $this->_promptMsg($collect, $play);
    $this->_mockSuccessResponse($msg, self::$success);

    $this->call->promptAudio($collect, 'audio.mp3')->done([$this, '__syncPromptCheck']);
    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testPromptAudioWithFlattenedParams(): void {
    $collect = ['initial_timeout' => 10, 'digits' => [ 'max' => 3 ]];
    $play = [
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']]
    ];
    $msg = $this->_promptMsg($collect, $play);
    $this->_mockSuccessResponse($msg, self::$success);

    $params = ['initial_timeout' => 10, 'digits_max' => 3, 'url' => 'audio.mp3'];
    $this->call->promptAudio($params)->done([$this, '__syncPromptCheck']);
    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testPromptAudioAsync(): void {
    $collect = ['initial_timeout' => 10, 'digits' => [ 'max' => 3 ]];
    $play = [
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']]
    ];
    $msg = $this->_promptMsg($collect, $play);
    $this->_mockSuccessResponse($msg, self::$success);

    $this->call->promptAudioAsync($collect, 'audio.mp3')->done([$this, '__asyncPromptCheck']);
  }

  public function testPromptAudioAsyncWithFlattenedParams(): void {
    $collect = ['initial_timeout' => 10, 'digits' => [ 'max' => 3 ]];
    $play = [
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']]
    ];
    $msg = $this->_promptMsg($collect, $play);
    $this->_mockSuccessResponse($msg, self::$success);

    $params = ['initial_timeout' => 10, 'digits_max' => 3, 'url' => 'audio.mp3'];
    $this->call->promptAudioAsync($params)->done([$this, '__asyncPromptCheck']);
  }

  public function testPromptRingtone(): void {
    $collect = ['digits' => [ 'max' => 3 ]];
    $play = [
      ['type' => 'ringtone', 'params' => ['name' => 'at', 'duration' => 3.5]]
    ];
    $msg = $this->_promptMsg($collect, $play);
    $this->_mockSuccessResponse($msg, self::$success);

    $params = ['digits_max' => 3, 'name' => 'at', 'duration' => '3.5'];
    $this->call->promptRingtone($params)->done([$this, '__syncPromptCheck']);
    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testPromptRingtoneWithVolume(): void {
    $collect = ['initial_timeout' => 10, 'digits' => [ 'max' => 3 ], 'volume' => 5.4];
    $play = [
      ['type' => 'ringtone', 'params' => ['name' => 'at', 'duration' => 3.5]]
    ];
    $msg = $this->_promptMsg($collect, $play);
    $this->_mockSuccessResponse($msg, self::$success);

    $params = ['initial_timeout' => 10, 'digits_max' => 3, 'name' => 'at', 'duration' => '3.5', 'volume' => '5.4'];
    $this->call->promptRingtone($params)->done([$this, '__syncPromptCheck']);
    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testPromptRingtoneAsync(): void {
    $collect = ['digits' => [ 'max' => 3 ]];
    $play = [
      ['type' => 'ringtone', 'params' => ['name' => 'at', 'duration' => 3.5]]
    ];
    $msg = $this->_promptMsg($collect, $play);
    $this->_mockSuccessResponse($msg, self::$success);

    $params = ['digits_max' => 3, 'name' => 'at', 'duration' => '3.5'];
    $this->call->promptRingtoneAsync($params)->done([$this, '__asyncPromptCheck']);
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

    $this->calling->notificationHandler(self::$notificationFinished);

    $this->assertTrue($action->isCompleted());
  }

  private function _promptMsg(Array $collect, Array $play) {
    $volume = isset($collect['volume']) ? $collect['volume'] : 0;
    unset($collect['volume']);
    $params = [
      'call_id' => 'call-id',
      'node_id' => 'node-id',
      'control_id' => self::UUID,
      'collect' => $collect,
      'play' => $play
    ];
    if ($volume !== 0) {
      $params['volume'] = $volume;
    }
    return new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'calling.play_and_collect',
      'params' => $params
    ]);
  }
}
