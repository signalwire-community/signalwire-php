<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use SignalWire\Messages\Execute;

class RelayCallingCallSendDigitsTest extends RelayCallingBaseActionCase
{
  protected static $notificationFinished;
  public static $success;
  public static $fail;

  public static function setUpBeforeClass() {
    self::$notificationFinished = json_decode('{"event_type":"calling.call.send_digits","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","state":"finished"}}');
    self::$success = json_decode('{"result":{"code":"200","message":"message","control_id":"'.self::UUID.'"}}');
    self::$fail = json_decode('{"result":{"code":"400","message":"some error","control_id":"'.self::UUID.'"}}');
  }

  protected function setUp() {
    parent::setUp();

    $this->_setCallReady();
  }

  public function testSendDigitsSuccess(): void {
    $msg = $this->_sendDigitsMsg();
    $this->_mockSuccessResponse($msg, self::$success);
    $this->call->sendDigits('1234')->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\SendDigitsResult', $result);
      $this->assertTrue($result->isSuccessful());
      $this->assertObjectHasAttribute('state', $result->getEvent()->payload);
    });
    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testSendDigitsFail(): void {
    $msg = $this->_sendDigitsMsg();
    $this->_mockFailResponse($msg, self::$fail);
    $this->call->sendDigits('1234')->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\SendDigitsResult', $result);
      $this->assertFalse($result->isSuccessful());
    });
  }

  public function testSendDigitsAsyncSuccess(): void {
    $msg = $this->_sendDigitsMsg();
    $this->_mockSuccessResponse($msg, self::$success);
    $this->call->sendDigitsAsync('1234')->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\SendDigitsAction', $action);
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\SendDigitsResult', $action->getResult());
      $this->assertFalse($action->isCompleted());
      $this->calling->notificationHandler(self::$notificationFinished);
      $this->assertTrue($action->isCompleted());
    });
  }

  public function testSendDigitsAsyncFail(): void {
    $msg = $this->_sendDigitsMsg();
    $this->_mockFailResponse($msg, self::$fail);
    $this->call->sendDigitsAsync('1234')->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\SendDigitsAction', $action);
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getState(), 'failed');
    });
  }

  private function _sendDigitsMsg() {
    return new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'calling.send_digits',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'digits' => '1234'
      ]
    ]);
  }
}
