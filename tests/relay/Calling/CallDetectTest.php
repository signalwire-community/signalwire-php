<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use SignalWire\Messages\Execute;

class RelayCallingCallDetectTest extends RelayCallingBaseActionCase
{
  protected static $notificationFaxCED;
  protected static $notificationFaxError;
  protected static $notificationFaxFinished;
  protected static $notificationMachineMachine;
  protected static $notificationMachineUnknown;
  protected static $notificationMachineHuman;
  protected static $notificationMachineReady;
  protected static $notificationMachineNotReady;
  protected static $notificationMachineError;
  protected static $notificationMachineFinished;
  protected static $notificationDigitDTMF;
  protected static $notificationDigitError;
  protected static $notificationDigitFinished;

  public static function setUpBeforeClass() {
    self::$notificationFaxCED = json_decode('{"event_type":"calling.call.detect","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","detect":{"type":"fax","params":{"event":"CED"}}}}');
    self::$notificationFaxError = json_decode('{"event_type":"calling.call.detect","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","detect":{"type":"fax","params":{"event":"error"}}}}');
    self::$notificationFaxFinished = json_decode('{"event_type":"calling.call.detect","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","detect":{"type":"fax","params":{"event":"finished"}}}}');

    self::$notificationMachineMachine = json_decode('{"event_type":"calling.call.detect","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","detect":{"type":"machine","params":{"event":"MACHINE"}}}}');
    self::$notificationMachineUnknown = json_decode('{"event_type":"calling.call.detect","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","detect":{"type":"machine","params":{"event":"UNKNOWN"}}}}');
    self::$notificationMachineHuman = json_decode('{"event_type":"calling.call.detect","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","detect":{"type":"machine","params":{"event":"HUMAN"}}}}');
    self::$notificationMachineReady = json_decode('{"event_type":"calling.call.detect","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","detect":{"type":"machine","params":{"event":"READY"}}}}');
    self::$notificationMachineNotReady = json_decode('{"event_type":"calling.call.detect","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","detect":{"type":"machine","params":{"event":"NOT_READY"}}}}');
    self::$notificationMachineError = json_decode('{"event_type":"calling.call.detect","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","detect":{"type":"machine","params":{"event":"error"}}}}');
    self::$notificationMachineFinished = json_decode('{"event_type":"calling.call.detect","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","detect":{"type":"machine","params":{"event":"finished"}}}}');

    self::$notificationDigitDTMF = json_decode('{"event_type":"calling.call.detect","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","detect":{"type":"digit","params":{"event":"1#"}}}}');
    self::$notificationDigitError = json_decode('{"event_type":"calling.call.detect","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","detect":{"type":"digit","params":{"event":"error"}}}}');
    self::$notificationDigitFinished = json_decode('{"event_type":"calling.call.detect","params":{"control_id":"'.self::UUID.'","call_id":"call-id","node_id":"node-id","detect":{"type":"digit","params":{"event":"finished"}}}}');
  }

  protected function setUp() {
    parent::setUp();

    $this->_setCallReady();
  }

  public function testDetectSuccessWithTypeFax(): void {
    $msg = $this->_detectMsg('fax');
    $this->_mockSuccessResponse($msg);
    $params = [ 'type' => 'fax', 'timeout' => 25];
    $this->call->detect($params)->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $result);
      $this->assertTrue($result->isSuccessful());
      $this->assertEquals($result->getType(), 'fax');
      $this->assertEquals($result->getResult(), 'CED');
      $this->assertObjectHasAttribute('type', $result->getEvent()->payload);
      $this->assertObjectHasAttribute('params', $result->getEvent()->payload);
    });
    $this->calling->notificationHandler(self::$notificationFaxCED);
  }

  public function testDetectSuccessWithTypeMachine(): void {
    $msg = $this->_detectMsg('machine');
    $this->_mockSuccessResponse($msg);
    $params = [ 'type' => 'machine', 'timeout' => 25];
    $this->call->detect($params)->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $result);
      $this->assertTrue($result->isSuccessful());
      $this->assertEquals($result->getType(), 'machine');
      $this->assertEquals($result->getResult(), 'MACHINE');
      $this->assertObjectHasAttribute('type', $result->getEvent()->payload);
      $this->assertObjectHasAttribute('params', $result->getEvent()->payload);
    });
    $this->calling->notificationHandler(self::$notificationMachineMachine);
  }

  public function testDetectSuccessWithTypeDigit(): void {
    $msg = $this->_detectMsg('digit');
    $this->_mockSuccessResponse($msg);
    $params = [ 'type' => 'digit', 'timeout' => 25];
    $this->call->detect($params)->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $result);
      $this->assertTrue($result->isSuccessful());
      $this->assertEquals($result->getType(), 'digit');
      $this->assertEquals($result->getResult(), '1#');
      $this->assertObjectHasAttribute('type', $result->getEvent()->payload);
      $this->assertObjectHasAttribute('params', $result->getEvent()->payload);
    });
    $this->calling->notificationHandler(self::$notificationDigitDTMF);
  }

  public function testDetectFailOnTimeout(): void {
    $msg = $this->_detectMsg('fax');
    $this->_mockSuccessResponse($msg);
    $params = ['type' => 'fax', 'timeout' => 25];
    $this->call->detect($params)->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $result);
      $this->assertFalse($result->isSuccessful());
      $this->assertEquals($result->getType(), 'fax');
      $this->assertEquals($result->getResult(), '');
      $this->assertObjectHasAttribute('type', $result->getEvent()->payload);
      $this->assertObjectHasAttribute('params', $result->getEvent()->payload);
    });
    $this->calling->notificationHandler(self::$notificationFaxFinished);
  }

  public function testDetectFailOnError(): void {
    $msg = $this->_detectMsg('fax');
    $this->_mockSuccessResponse($msg);
    $params = [ 'type' => 'fax', 'timeout' => 25];
    $this->call->detect($params)->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $result);
      $this->assertFalse($result->isSuccessful());
      $this->assertEquals($result->getType(), 'fax');
      $this->assertEquals($result->getResult(), '');
      $this->assertObjectHasAttribute('type', $result->getEvent()->payload);
      $this->assertObjectHasAttribute('params', $result->getEvent()->payload);
    });
    $this->calling->notificationHandler(self::$notificationFaxError);
  }

  public function testDetectFail(): void {
    $msg = $this->_detectMsg('fax');
    $this->_mockFailResponse($msg);
    $params = [ 'type' => 'fax', 'timeout' => 25];
    $this->call->detect($params)->done([$this, '__detectFailCheck']);
  }

  public function testDetectAsyncSuccess(): void {
    $msg = $this->_detectMsg('fax', ['tone' => 'CED'], 45);
    $this->_mockSuccessResponse($msg);
    $params = [ 'type' => 'fax', 'timeout' => 45, 'tone' => 'CED' ];
    $this->call->detectAsync($params)->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\DetectAction', $action);
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $action->getResult());
      $this->assertFalse($action->isCompleted());
      $this->calling->notificationHandler(self::$notificationFaxCED);
      $this->assertFalse($action->isCompleted());
      $this->calling->notificationHandler(self::$notificationFaxFinished);
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getResult()->getResult(), 'CED');
    });
  }

  public function testDetectAsyncFail(): void {
    $msg = $this->_detectMsg('fax',['tone' => 'CED'], 45);
    $this->_mockFailResponse($msg);
    $params = [ 'type' => 'fax', 'timeout' => 45, 'tone' => 'CED' ];
    $this->call->detectAsync($params)->done([$this, '__detectAsyncFailCheck']);
  }

  public function testDetectHumanSuccess(): void {
    $msg = $this->_detectMsg('machine');
    $this->_mockSuccessResponse($msg);
    @$this->call->detectHuman(['timeout' => 25])->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $result);
      $this->assertTrue($result->isSuccessful());
      $this->assertEquals($result->getType(), 'machine');
      $this->assertEquals($result->getResult(), 'HUMAN');
      $this->assertObjectHasAttribute('type', $result->getEvent()->payload);
      $this->assertObjectHasAttribute('params', $result->getEvent()->payload);
    });
    $this->calling->notificationHandler(self::$notificationMachineHuman);
  }

  public function testDetectHumanFailIfMachineOrUnknown(): void {
    $msg = $this->_detectMsg('machine');
    $this->_mockSuccessResponse($msg);
    @$this->call->detectHuman(['timeout' => 25])->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $result);
      $this->assertFalse($result->isSuccessful());
      $this->assertEquals($result->getType(), 'machine');
      $this->assertEquals($result->getResult(), 'UNKNOWN');
      $this->assertObjectHasAttribute('type', $result->getEvent()->payload);
      $this->assertObjectHasAttribute('params', $result->getEvent()->payload);
    });
    $this->calling->notificationHandler(self::$notificationMachineUnknown);
  }

  public function testDetectHumanFail(): void {
    $msg = $this->_detectMsg('machine');
    $this->_mockFailResponse($msg);
    @$this->call->detectHuman(['timeout' => 25])->done([$this, '__detectFailCheck']);
  }

  public function testDetectHumanAsyncSuccess(): void {
    $msg = $this->_detectMsg('machine', ['initial_timeout' => 5], 45);
    $this->_mockSuccessResponse($msg);
    @$this->call->detectHumanAsync(['initial_timeout' => 5, 'timeout' => 45])->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\DetectAction', $action);
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $action->getResult());
      $this->calling->notificationHandler(self::$notificationMachineUnknown);
      $this->calling->notificationHandler(self::$notificationMachineHuman);
      $this->assertFalse($action->isCompleted());
      $this->calling->notificationHandler(self::$notificationMachineFinished);
      $this->assertTrue($action->isCompleted());
    });
  }

  public function testDetectHumanAsyncFail(): void {
    $msg = $this->_detectMsg('machine', ['initial_timeout' => 5], 45);
    $this->_mockFailResponse($msg);
    @$this->call->detectHumanAsync(['initial_timeout' => 5, 'timeout' => 45])->done([$this, '__detectAsyncFailCheck']);
  }

  public function testDetectMachineSuccess(): void {
    $msg = $this->_detectMsg('machine');
    $this->_mockSuccessResponse($msg);

    @$this->call->detectMachine(['timeout' => 25])->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $result);
      $this->assertTrue($result->isSuccessful());
      $this->assertEquals($result->getType(), 'machine');
      $this->assertEquals($result->getResult(), 'MACHINE');
      $this->assertObjectHasAttribute('type', $result->getEvent()->payload);
      $this->assertObjectHasAttribute('params', $result->getEvent()->payload);
    });
    $this->calling->notificationHandler(self::$notificationMachineMachine);
  }

  public function testDetectMachineFailIfHumanOrUnknown(): void {
    $msg = $this->_detectMsg('machine');
    $this->_mockSuccessResponse($msg);

    @$this->call->detectMachine(['timeout' => 25])->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $result);
      $this->assertFalse($result->isSuccessful());
      $this->assertEquals($result->getType(), 'machine');
      $this->assertEquals($result->getResult(), 'UNKNOWN');
      $this->assertObjectHasAttribute('type', $result->getEvent()->payload);
      $this->assertObjectHasAttribute('params', $result->getEvent()->payload);
    });
    $this->calling->notificationHandler(self::$notificationMachineUnknown);
  }

  public function testDetectMachineFailOnTimeout(): void {
    $msg = $this->_detectMsg('machine');
    $this->_mockSuccessResponse($msg);

    @$this->call->detectMachine(['timeout' => 25])->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $result);
      $this->assertFalse($result->isSuccessful());
      $this->assertEquals($result->getType(), 'machine');
      $this->assertEquals($result->getResult(), '');
      $this->assertObjectHasAttribute('type', $result->getEvent()->payload);
      $this->assertObjectHasAttribute('params', $result->getEvent()->payload);
    });
    $this->calling->notificationHandler(self::$notificationMachineFinished);
  }

  public function testDetectMachineFail(): void {
    $msg = $this->_detectMsg('machine');
    $this->_mockFailResponse($msg);
    @$this->call->detectMachine(['timeout' => 25])->done([$this, '__detectFailCheck']);
  }

  public function testDetectMachineAsyncSuccess(): void {
    $msg = $this->_detectMsg('machine', ['initial_timeout' => 4], 45);
    $this->_mockSuccessResponse($msg);

    @$this->call->detectMachineAsync(['initial_timeout' => 4, 'timeout' => 45])->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\DetectAction', $action);
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $action->getResult());
      $this->assertFalse($action->isCompleted());
      $this->calling->notificationHandler(self::$notificationMachineMachine);
      $this->calling->notificationHandler(self::$notificationMachineNotReady);
      $this->calling->notificationHandler(self::$notificationMachineReady);
      $this->assertFalse($action->isCompleted());
      $this->calling->notificationHandler(self::$notificationMachineFinished);
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getResult()->getResult(), 'MACHINE,NOT_READY,READY');
    });
  }

  public function testDetectMachineAsyncFail(): void {
    $msg = $this->_detectMsg('machine',['initial_timeout' => 4], 45);
    $this->_mockFailResponse($msg);
    @$this->call->detectMachineAsync(['initial_timeout' => 4, 'timeout' => 45])->done([$this, '__detectAsyncFailCheck']);
  }

  public function testDetectDigitSuccess(): void {
    $msg = $this->_detectMsg('digit');
    $this->_mockSuccessResponse($msg);

    $this->call->detectDigit(['timeout' => 25])->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $result);
      $this->assertTrue($result->isSuccessful());
      $this->assertEquals($result->getType(), 'digit');
      $this->assertEquals($result->getResult(), '1#');
      $this->assertObjectHasAttribute('type', $result->getEvent()->payload);
      $this->assertObjectHasAttribute('params', $result->getEvent()->payload);
    });
    $this->calling->notificationHandler(self::$notificationDigitDTMF);
  }

  public function testDetectDigitFail(): void {
    $msg = $this->_detectMsg('digit');
    $this->_mockFailResponse($msg);
    $this->call->detectDigit(['timeout' => 25])->done([$this, '__detectFailCheck']);
  }

  public function testDetectDigitAsyncSuccess(): void {
    $msg = $this->_detectMsg('digit', ['digits' => '123'], 45);
    $this->_mockSuccessResponse($msg);

    $this->call->detectDigitAsync(['digits' => '123', 'timeout' => 45])->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\DetectAction', $action);
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $action->getResult());
      $this->calling->notificationHandler(self::$notificationDigitDTMF);
      $this->assertFalse($action->isCompleted());
      $this->calling->notificationHandler(self::$notificationDigitFinished);
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getResult()->getResult(), '1#');
    });
  }

  public function testDetectDigitAsyncFail(): void {
    $msg = $this->_detectMsg('digit', ['digits' => '123'], 45);
    $this->_mockFailResponse($msg);
    $this->call->detectDigitAsync(['digits' => '123', 'timeout' => 45])->done([$this, '__detectAsyncFailCheck']);
  }

  public function testDetectFaxSuccess(): void {
    $msg = $this->_detectMsg('fax');
    $this->_mockSuccessResponse($msg);

    $this->call->detectFax(['timeout' => 25])->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $result);
      $this->assertTrue($result->isSuccessful());
      $this->assertEquals($result->getType(), 'fax');
      $this->assertEquals($result->getResult(), 'CED');
      $this->assertObjectHasAttribute('type', $result->getEvent()->payload);
      $this->assertObjectHasAttribute('params', $result->getEvent()->payload);
    });
    $this->calling->notificationHandler(self::$notificationFaxCED);
  }

  public function testDetectFaxFail(): void {
    $msg = $this->_detectMsg('fax');
    $this->_mockFailResponse($msg);
    $this->call->detectFax(['timeout' => 25])->done([$this, '__detectFailCheck']);
  }

  public function testDetectFaxAsyncSuccess(): void {
    $msg = $this->_detectMsg('fax', ['tone' => 'CED'], 45);
    $this->_mockSuccessResponse($msg);

    $this->call->detectFaxAsync(['tone' => 'CED', 'timeout' => 45])->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\DetectAction', $action);
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $action->getResult());
      $this->calling->notificationHandler(self::$notificationFaxCED);
      $this->assertFalse($action->isCompleted());
      $this->calling->notificationHandler(self::$notificationFaxFinished);
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getResult()->getResult(), 'CED');
    });
  }

  public function testDetectFaxAsyncFail(): void {
    $msg = $this->_detectMsg('fax', ['tone' => 'CED'], 45);
    $this->_mockFailResponse($msg);
    $this->call->detectFaxAsync(['tone' => 'CED', 'timeout' => 45])->done([$this, '__detectAsyncFailCheck']);
  }

  public function testDetectAnsweringMachine(): void {
    $msg = $this->_detectMsg('machine');
    $this->_mockSuccessResponse($msg);
    $this->call->detectAnsweringMachine(['timeout' => 25])->done(function ($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $result);
      $this->assertTrue($result->isSuccessful());
      $this->assertEquals($result->getType(), 'machine');
      $this->assertEquals($result->getResult(), 'MACHINE');
      $this->assertObjectHasAttribute('type', $result->getEvent()->payload);
      $this->assertObjectHasAttribute('params', $result->getEvent()->payload);
    });
    $this->calling->notificationHandler(self::$notificationMachineMachine);
  }

  public function testDetectAnsweringMachineFail(): void {
    $msg = $this->_detectMsg('machine');
    $this->_mockFailResponse($msg);
    $this->call->detectAnsweringMachine(['timeout' => 25])->done([$this, '__detectFailCheck']);
  }

  public function testDetectAnsweringMachineWaitingForBeep(): void {
    $msg = $this->_detectMsg('machine');
    $this->_mockSuccessResponse($msg);
    $this->call->detectAnsweringMachine(['timeout' => 25, 'wait_for_beep' => true])->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $result);
      $this->assertTrue($result->isSuccessful());
      $this->assertEquals($result->getType(), 'machine');
      $this->assertEquals($result->getResult(), 'MACHINE');
      $this->assertObjectHasAttribute('type', $result->getEvent()->payload);
      $this->assertObjectHasAttribute('params', $result->getEvent()->payload);
      $this->assertEquals('READY', $result->getEvent()->payload->params->event);
    });
    $this->calling->notificationHandler(self::$notificationMachineMachine);
    $this->calling->notificationHandler(self::$notificationMachineNotReady);
    $this->calling->notificationHandler(self::$notificationMachineReady);
    $this->calling->notificationHandler(self::$notificationMachineNotReady); // This will be ignored by Detect component
  }

  public function testDetectAnsweringMachineWaitingForBeepReceivingHuman(): void {
    $msg = $this->_detectMsg('machine');
    $this->_mockSuccessResponse($msg);
    $this->call->detectAnsweringMachine(['timeout' => 25, 'wait_for_beep' => true])->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $result);
      $this->assertTrue($result->isSuccessful());
      $this->assertEquals($result->getType(), 'machine');
      $this->assertEquals($result->getResult(), 'HUMAN');
      $this->assertObjectHasAttribute('type', $result->getEvent()->payload);
      $this->assertObjectHasAttribute('params', $result->getEvent()->payload);
      $this->assertEquals('HUMAN', $result->getEvent()->payload->params->event);
    });
    $this->calling->notificationHandler(self::$notificationMachineHuman);
  }

  public function testDetectAnsweringMachineFailOnTimeout(): void {
    $msg = $this->_detectMsg('machine');
    $this->_mockSuccessResponse($msg);
    $this->call->detectAnsweringMachine(['timeout' => 25])->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $result);
      $this->assertFalse($result->isSuccessful());
      $this->assertEquals($result->getType(), 'machine');
      $this->assertEquals($result->getResult(), '');
      $this->assertObjectHasAttribute('type', $result->getEvent()->payload);
      $this->assertObjectHasAttribute('params', $result->getEvent()->payload);
    });
    $this->calling->notificationHandler(self::$notificationMachineFinished);
  }

  public function testDetectAnsweringMachineAsyncSuccess(): void {
    $msg = $this->_detectMsg('machine', [], 45);
    $this->_mockSuccessResponse($msg);
    $this->call->detectAnsweringMachineAsync(['timeout' => 45])->done(function ($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\DetectAction', $action);
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $action->getResult());
      $this->assertFalse($action->isCompleted());
      $this->calling->notificationHandler(self::$notificationMachineMachine);
      $this->calling->notificationHandler(self::$notificationMachineNotReady);
      $this->calling->notificationHandler(self::$notificationMachineReady);
      $this->calling->notificationHandler(self::$notificationMachineNotReady);
      $this->assertFalse($action->isCompleted());
      $this->calling->notificationHandler(self::$notificationFaxFinished);
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getResult()->getResult(), 'MACHINE,NOT_READY,READY,NOT_READY');
    });
  }

  public function testDetectAnsweringMachineAsyncFail(): void {
    $msg = $this->_detectMsg('machine', [], 45);
    $this->_mockFailResponse($msg);
    $this->call->detectAnsweringMachineAsync(['timeout' => 45])->done([$this, '__detectAsyncFailCheck']);
  }

  /**
   * Private to not repeat the same function for every sync fail
   */
  public function __detectFailCheck($result) {
    $this->assertInstanceOf('SignalWire\Relay\Calling\Results\DetectResult', $result);
    $this->assertFalse($result->isSuccessful());
  }

  /**
   * Private to not repeat the same function for every Async fail
   */
  public function __detectAsyncFailCheck($action) {
    $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\DetectAction', $action);
    $this->assertTrue($action->isCompleted());
    $this->assertEquals($action->getState(), 'failed');
  }

  private function _detectMsg($type, $params = [], $timeout = 25) {
    return new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'calling.detect',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'detect' => ['type' => $type, 'params' => (object)$params],
        'timeout' => $timeout
      ]
    ]);
  }
}
