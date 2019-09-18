<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use SignalWire\Messages\Execute;

class RelayCallingCallRecordTest extends RelayCallingBaseActionCase
{
  protected static $notificationFinished;
  public static $success;
  public static $fail;

  public static function setUpBeforeClass() {
    self::$notificationFinished = json_decode('{"event_type":"calling.call.record","params":{"state":"finished","record":{"audio":{"format":"mp3","direction":"speak","stereo":false}},"url":"record.mp3","control_id":"'.self::UUID.'","size":4096,"duration":4,"call_id":"call-id","node_id":"node-id"}}');
    self::$success = json_decode('{"result":{"code":"200","message":"message","control_id":"'.self::UUID.'","url":"record.mp3"}}');
    self::$fail = json_decode('{"result":{"code":"400","message":"some error","control_id":"'.self::UUID.'"}}');
  }

  protected function setUp() {
    parent::setUp();

    $this->_setCallReady();
  }

  public function testRecordSuccess(): void {
    $msg = $this->_recordMsg();
    $this->_mockSuccessResponse($msg, self::$success);

    $record = ['audio' => ['beep' => true, 'stereo' => false]];
    $this->call->record($record)->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\RecordResult', $result);
      $this->assertTrue($result->isSuccessful());
      $this->assertEquals($result->getUrl(), 'record.mp3');
      $this->assertEquals($result->getSize(), 4096);
      $this->assertObjectHasAttribute('url', $result->getEvent()->payload);
    });

    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testRecordSuccessWithFlattenedParams(): void {
    $msg = $this->_recordMsg();
    $this->_mockSuccessResponse($msg, self::$success);

    $record = ['beep' => true, 'stereo' => false];
    $this->call->record($record)->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\RecordResult', $result);
      $this->assertTrue($result->isSuccessful());
      $this->assertEquals($result->getUrl(), 'record.mp3');
      $this->assertEquals($result->getSize(), 4096);
      $this->assertObjectHasAttribute('url', $result->getEvent()->payload);
    });

    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testRecordFail(): void {
    $msg = $this->_recordMsg();
    $this->_mockFailResponse($msg, self::$fail);

    $record = ['audio' => ['beep' => true, 'stereo' => false]];
    $this->call->record($record)->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\RecordResult', $result);
      $this->assertFalse($result->isSuccessful());
    });

    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testRecordAsyncSuccess(): void {
    $msg = $this->_recordMsg();
    $this->_mockSuccessResponse($msg, self::$success);

    $record = ['audio' => ['beep' => true, 'stereo' => false]];
    $this->call->recordAsync($record)->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\RecordAction', $action);
      $this->assertEquals($action->getUrl(), 'record.mp3');
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\RecordResult', $action->getResult());
      $this->assertFalse($action->isCompleted());

      $this->calling->notificationHandler(self::$notificationFinished);

      $this->assertTrue($action->isCompleted());
    });
  }

  public function testRecordAsyncSuccessWithFlattenedParams(): void {
    $msg = $this->_recordMsg();
    $this->_mockSuccessResponse($msg, self::$success);

    $record = ['beep' => true, 'stereo' => false];
    $this->call->recordAsync($record)->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\RecordAction', $action);
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\RecordResult', $action->getResult());
      $this->assertFalse($action->isCompleted());

      $this->calling->notificationHandler(self::$notificationFinished);

      $this->assertTrue($action->isCompleted());
    });
  }

  public function testRecordAsyncFail(): void {
    $msg = $this->_recordMsg();
    $this->_mockFailResponse($msg, self::$fail);

    $record = ['audio' => ['beep' => true, 'stereo' => false]];
    $this->call->recordAsync($record)->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\RecordAction', $action);
      $this->assertNull($action->getUrl());
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\RecordResult', $action->getResult());
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getState(), 'failed');
    });
  }

  private function _recordMsg() {
    return $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'calling.record',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'record' => ['audio' => ['beep' => true, 'stereo' => false]]
      ]
    ]);
  }
}
