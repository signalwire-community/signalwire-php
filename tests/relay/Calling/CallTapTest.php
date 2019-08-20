<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use SignalWire\Messages\Execute;

class RelayCallingCallTapTest extends RelayCallingBaseActionCase
{
  protected static $notificationFinished;
  public static $success;
  public static $fail;
  protected static $tap;
  protected static $device;

  public static function setUpBeforeClass() {
    self::$notificationFinished = json_decode('{"event_type":"calling.call.tap","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","state":"finished","tap":{"type":"audio","params":{"direction":"listen"}},"device":{"type":"rtp","params":{"addr":"127.0.0.1","port":"1234","codec":"PCMU","ptime":"20"}}}}');
    self::$success = json_decode('{"result":{"code":"200","message":"message","control_id":"'.self::UUID.'","source_device":{"type":"rtp","params":{"addr":"10.10.10.10","port":3000,"codec":"PCMU","rate":8000}}}}');
    self::$fail = json_decode('{"result":{"code":"400","message":"some error","control_id":"'.self::UUID.'"}}');
    self::$tap = ['type' => 'audio'];
    self::$device = ['type' => 'rtp', 'addr' => '127.0.0.1', 'port' => 1234];
  }

  protected function setUp() {
    parent::setUp();

    $this->_setCallReady();
  }

  public function testTapSuccess(): void {
    $msg = $this->_tapMsg();
    $this->_mockSuccessResponse($msg, self::$success);
    $this->call->tap(self::$tap, self::$device)->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\TapResult', $result);
      $this->assertTrue($result->isSuccessful());
      $this->assertEquals($result->getTap(), json_decode('{"type":"audio","params":{"direction":"listen"}}'));
      $this->assertEquals($result->getSourceDevice(), self::$success->result->source_device);
      $this->assertEquals($result->getDestinationDevice(), json_decode('{"type":"rtp","params":{"addr":"127.0.0.1","port":"1234","codec":"PCMU","ptime":"20"}}'));
      $this->assertObjectHasAttribute('tap', $result->getEvent()->payload);
      $this->assertObjectHasAttribute('device', $result->getEvent()->payload);
    });
    $this->calling->notificationHandler(self::$notificationFinished);
  }

  public function testTapFail(): void {
    $msg = $this->_tapMsg();
    $this->_mockFailResponse($msg, self::$fail);
    $this->call->tap(self::$tap, self::$device)->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\TapResult', $result);
      $this->assertFalse($result->isSuccessful());
    });
  }

  public function testTapAsyncSuccess(): void {
    $msg = $this->_tapMsg();
    $this->_mockSuccessResponse($msg, self::$success);
    $this->call->tapAsync(self::$tap, self::$device)->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\TapAction', $action);
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\TapResult', $action->getResult());
      $this->assertFalse($action->isCompleted());
      $this->calling->notificationHandler(self::$notificationFinished);
      $this->assertTrue($action->isCompleted());
    });
  }

  public function testTapAsyncFail(): void {
    $msg = $this->_tapMsg();
    $this->_mockFailResponse($msg, self::$fail);
    $this->call->tapAsync(self::$tap, self::$device)->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\TapAction', $action);
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getState(), 'failed');
    });
  }

  private function _tapMsg() {
    return new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'calling.tap',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'tap' => ['type' => 'audio', 'params' => new \stdClass],
        'device' => ['type' => 'rtp', 'params' => (object)['addr' => '127.0.0.1', 'port' => 1234]]
      ]
    ]);
  }
}
