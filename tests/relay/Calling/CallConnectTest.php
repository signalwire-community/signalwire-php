<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use SignalWire\Messages\Execute;
use SignalWire\Relay\Calling\Devices\DeviceFactory;

class RelayCallingCallConnectTest extends RelayCallingBaseActionCase
{
  protected static $connectDeviceFirst;
  protected static $connectDeviceSecond;
  protected static $notificationConnect;
  protected static $notificationPeerCreated;
  protected static $notificationFailed;

  public static function setUpBeforeClass() {
    self::$connectDeviceFirst = DeviceFactory::create(['type' => 'phone', 'params' => ['to_number' => '999', 'from_number' => '231', 'timeout' => 10]]);
    self::$connectDeviceSecond = DeviceFactory::create(['type' => 'phone', 'params' => ['to_number' => '888', 'from_number' => '+88800000000', 'timeout' => 20]]);
    self::$notificationConnect = json_decode('{"event_type":"calling.call.connect","params":{"connect_state":"connected","peer":{"call_id":"peer-call-id","node_id":"peer-node-id","device":{"type":"phone","params":{"from_number":"+1234","to_number":"+15678"}}},"call_id":"call-id","node_id":"node-id"}}');
    self::$notificationPeerCreated = json_decode('{"event_type":"calling.call.state","params":{"call_state":"created","direction":"outbound","device":{"type":"phone","params":{"from_number":"+1234","to_number":"15678"}},"peer":{"call_id":"call-id","node_id":"node-id"},"call_id":"peer-call-id","node_id":"peer-node-id"}}');
    self::$notificationFailed = json_decode('{"event_type":"calling.call.connect","params":{"connect_state":"failed","peer":{"call_id":"peer-call-id","node_id":"peer-node-id"},"call_id":"call-id","node_id":"node-id"}}');
  }

  protected function setUp() {
    parent::setUp();

    $this->_setCallReady();
  }

  public function testConnectSyncFail(): void {
    $msg = $this->_connectMsg([
      [self::$connectDeviceFirst], [self::$connectDeviceSecond]
    ]);

    $this->_mockFailResponse($msg);

    $this->call->connect(
      [ 'type' => 'phone', 'to' => '999', 'from' => '231', 'timeout' => 10 ],
      [ 'type' => 'phone', 'to' => '888' ]
    )->done(function ($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\ConnectResult', $result);
      $this->assertNull($result->getCall());
      $this->assertFalse($result->isSuccessful());
    });

    $this->calling->notificationHandler(self::$notificationPeerCreated);
    $this->calling->notificationHandler(self::$notificationConnect);
  }

  public function testConnectAsyncFail(): void {
    $msg = $this->_connectMsg([
      [self::$connectDeviceFirst], [self::$connectDeviceSecond]
    ]);

    $this->_mockFailResponse($msg);

    $this->call->connectAsync(
      [ 'type' => 'phone', 'to' => '999', 'from' => '231', 'timeout' => 10 ],
      [ 'type' => 'phone', 'to' => '888' ]
    )->done(function ($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\ConnectAction', $action);
      $this->assertTrue($action->isCompleted());
    });

    $this->calling->notificationHandler(self::$notificationPeerCreated);
    $this->calling->notificationHandler(self::$notificationConnect);
  }

  public function testConnectDevicesInSeries(): void {
    $msg = $this->_connectMsg([
      [self::$connectDeviceFirst], [self::$connectDeviceSecond]
    ]);
    $this->_mockSuccessResponse($msg);

    $this->call->connect(
      [ 'type' => 'phone', 'to' => '999', 'from' => '231', 'timeout' => 10 ],
      [ 'type' => 'phone', 'to' => '888' ]
    )->done([$this, '__syncConnectCheck']);

    $this->calling->notificationHandler(self::$notificationPeerCreated);
    $this->calling->notificationHandler(self::$notificationConnect);
  }

  public function testConnectDevicesInSeriesWithRingback(): void {
    $devices = [
      [self::$connectDeviceFirst], [self::$connectDeviceSecond]
    ];
    $ringback = ['type' => 'ringtone', 'params' => ['name' => 'us', 'duration' => 10]];
    $msg = $this->_connectMsg($devices, $ringback);
    $this->_mockSuccessResponse($msg);

    $params = [
      'devices' => [
        ['type' => 'phone', 'to' => '999', 'from' => '231', 'timeout' => 10],
        ['type' => 'phone', 'to' => '888']
      ],
      'ringback' => [ 'type' => 'ringtone', 'name' => 'us', 'duration' => 10 ]
    ];
    $this->call->connect($params)->done([$this, '__syncConnectCheck']);

    $this->calling->notificationHandler(self::$notificationPeerCreated);
    $this->calling->notificationHandler(self::$notificationConnect);
  }

  public function testConnectDevicesInParallel(): void {
    $msg = $this->_connectMsg([
      [ self::$connectDeviceFirst, self::$connectDeviceSecond ]
    ]);

    $this->_mockSuccessResponse($msg);

    $this->call->connect(
      [
        [ 'type' => 'phone', 'to' => '999', 'from' => '231', 'timeout' => 10 ],
        [ 'type' => 'phone', 'to' => '888' ]
      ]
    )->done([$this, '__syncConnectCheck']);

    $this->calling->notificationHandler(self::$notificationPeerCreated);
    $this->calling->notificationHandler(self::$notificationConnect);
  }

  public function testConnectDevicesInParallelWithRingback(): void {
    $devices = [
      [self::$connectDeviceFirst, self::$connectDeviceSecond]
    ];
    $ringback = ['type' => 'ringtone', 'params' => ['name' => 'us', 'duration' => 10]];
    $msg = $this->_connectMsg($devices, $ringback);
    $this->_mockSuccessResponse($msg);

    $params = [
      'devices' => [
        [
          ['type' => 'phone', 'to' => '999', 'from' => '231', 'timeout' => 10],
          ['type' => 'phone', 'to' => '888']
        ]
      ],
      'ringback' => ['type' => 'ringtone', 'name' => 'us', 'duration' => 10],
    ];
    $this->call->connect($params)->done([$this, '__syncConnectCheck']);

    $this->calling->notificationHandler(self::$notificationPeerCreated);
    $this->calling->notificationHandler(self::$notificationConnect);
  }

  public function testConnectDevicesInBothSeriesAndParallel(): void {
    $msg = $this->_connectMsg([
      [
        DeviceFactory::create(['type' => 'phone', 'params' => ['to_number' => '999', 'from_number' => '+88800000000', 'timeout' => 20]])
      ],
      [
        DeviceFactory::create(['type' => 'phone', 'params' => ['to_number' => '555', 'from_number' => '+88800000000', 'timeout' => 20]])
      ],
      [
        DeviceFactory::create(['type' => 'phone', 'params' => ['to_number' => '999', 'from_number' => '231', 'timeout' => 10]]),
        DeviceFactory::create(['type' => 'phone', 'params' => ['to_number' => '888', 'from_number' => '+88800000000', 'timeout' => 20]])
      ]
    ]);

    $this->_mockSuccessResponse($msg);

    $this->call->connect(
      [
        [ 'type' => 'phone', 'to' => '999' ],
      ],
      [
        [ 'type' => 'phone', 'to' => '555' ],
      ],
      [
        [ 'type' => 'phone', 'to' => '999', 'from' => '231', 'timeout' => 10 ],
        [ 'type' => 'phone', 'to' => '888' ]
      ]
    )->done([$this, '__syncConnectCheck']);

    $this->calling->notificationHandler(self::$notificationPeerCreated);
    $this->calling->notificationHandler(self::$notificationConnect);
  }

  public function testConnectAsyncDevicesInSeries(): void {
    $msg = $this->_connectMsg([
      [ self::$connectDeviceFirst ], [ self::$connectDeviceSecond ]
    ]);
    $this->_mockSuccessResponse($msg);
    $this->call->connectAsync(
      [ 'type' => 'phone', 'to' => '999', 'from' => '231', 'timeout' => 10 ],
      [ 'type' => 'phone', 'to' => '888' ]
    )->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\ConnectAction', $action);
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\ConnectResult', $action->getResult());
      $this->assertFalse($action->isCompleted());
      $this->calling->notificationHandler(self::$notificationConnect);
      $this->assertEquals($action->getState(), 'connected');
      $this->assertTrue($action->isCompleted());
    });
  }

  public function testConnectAsyncDevicesInSeriesWithFailure(): void {
    $msg = $this->_connectMsg([
      [ self::$connectDeviceFirst ], [ self::$connectDeviceSecond ]
    ]);
    $this->_mockSuccessResponse($msg);
    $this->call->connectAsync(
      [ 'type' => 'phone', 'to' => '999', 'from' => '231', 'timeout' => 10 ],
      [ 'type' => 'phone', 'to' => '888' ]
    )->done(function($action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Actions\ConnectAction', $action);
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\ConnectResult', $action->getResult());
      $this->assertFalse($action->isCompleted());
      $this->calling->notificationHandler(self::$notificationFailed);
      $this->assertEquals($action->getState(), 'failed');
      $this->assertTrue($action->isCompleted());
      $this->assertFalse($action->getResult()->isSuccessful());
    });
  }

  /**
   * Callable to not repeat the same function for every SYNC connect test
   */
  public function __syncConnectCheck($result) {
    $this->assertInstanceOf('SignalWire\Relay\Calling\Results\ConnectResult', $result);
    $this->assertTrue($result->isSuccessful());
    $peerCall = $result->getCall();
    $this->assertEquals($peerCall, $this->call->peer);
    $this->assertEquals($peerCall->id, 'peer-call-id');
    $this->assertEquals($peerCall->peer, $this->call);
    $this->assertObjectHasAttribute('peer', $result->getEvent()->payload);
    $this->assertObjectHasAttribute('connect_state', $result->getEvent()->payload);
  }

  private function _connectMsg($devices, $ringback = null) {
    $params = [ 'call_id' => 'call-id', 'node_id' => 'node-id', 'devices' => $devices ];
    if ($ringback) {
      $params['ringback'] = $ringback;
    }
    return new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'calling.connect',
      'params' => $params
    ]);
  }
}
