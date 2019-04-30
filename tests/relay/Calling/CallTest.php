<?php

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Client;
use SignalWire\Relay\Calling\Call;
use SignalWire\Messages\Execute;

class RelayCallingCallTest extends TestCase
{
  const UUID = 'e36f227c-2946-11e8-b467-0ed5f89f718b';
  protected $call;
  protected $stub;

  protected function setUp() {
    $this->_mockUuid();
    $responseProto = json_decode('{"requester_nodeid":"ad490dc4-550a-4742-929d-b86fdf8958ef","responder_nodeid":"b0007713-071d-45f9-88aa-302d14e1251c","result":{"protocol":"signalwire_calling_proto"}}');
    $responseSubscr = json_decode('{"protocol":"signalwire_calling_proto","command":"add","subscribe_channels":["notifications"]}');
    $methodResponse = json_decode('{"requester_nodeid":"0ff2d880-c420-48c4-89b8-6d9d540d3b80","responder_nodeid":"1a9c9e34-892c-435c-9749-1f9e584bdae1","result":{"code":"200","message":"message"}}');

    $this->stub = $this->createMock(SignalWire\Relay\Connection::class, ['send']);
    $this->stub->method('send')->will($this->onConsecutiveCalls(
      \React\Promise\resolve($responseProto),
      \React\Promise\resolve($responseSubscr),
      \React\Promise\resolve($methodResponse)
    ));

    $client = new Client(array('host' => 'host', 'project' => 'project', 'token' => 'token'));
    $client->connection = $this->stub;

    $this->stub->expects($this->exactly(3))->method('send');

    $options = (object)[
      'device' => (object)[
        'type' => 'phone',
        'params' => (object)['from_number' => '234', 'to_number' => '456', 'timeout' => 20]
      ]
    ];
    $this->call = new Call($client->calling, $options);
  }

  public function tearDown() {
    unset($this->call);
    SignalWire\Handler::deRegisterAll('signalwire_calling_proto');
    \Ramsey\Uuid\Uuid::setFactory(new \Ramsey\Uuid\UuidFactory());
  }

  public function _setCallReady() {
    $this->call->id = 'call-id';
    $this->call->nodeId = 'node-id';
  }

  public function _mockUuid() {
    $factory = $this->createMock(\Ramsey\Uuid\UuidFactoryInterface::class);
    $factory->method('uuid4')
      ->will($this->returnValue(\Ramsey\Uuid\Uuid::fromString(self::UUID)));
    \Ramsey\Uuid\Uuid::setFactory($factory);
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

    $this->stub->expects($this->once())->method('send')->with($msg);

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

    $this->stub->expects($this->once())->method('send')->with($msg);

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

    $this->stub->expects($this->once())->method('send')->with($msg);

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

    $this->stub->expects($this->once())->method('send')->with($msg);

    $res = $this->call->playAudio('url-to-audio.mp3');
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
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

    $this->stub->expects($this->once())->method('send')->with($msg);

    $res = $this->call->playSilence(5);
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
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

    $this->stub->expects($this->once())->method('send')->with($msg);

    $res = $this->call->playTTS(['text' => 'Welcome', 'gender' => 'male']);
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
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

    $this->stub->expects($this->once())->method('send')->with($msg);

    $res = $this->call->playMedia(
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    );
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }

  public function testStopPlay(): void {
    $this->_setCallReady();
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play.stop',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => 'uuid'
      ]
    ]);

    $this->stub->expects($this->once())->method('send')->with($msg);

    $res = $this->call->stopPlay('uuid');
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }

  public function testStartRecord(): void {
    $this->_setCallReady();
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.record',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => self::UUID,
        'record' => [
          'type' => 'audio',
          'params' => ["beep" => true, "stereo" => false]
        ]
      ]
    ]);

    $this->stub->expects($this->once())->method('send')->with($msg);

    $res = $this->call->startRecord('audio', ["beep" => true, "stereo" => false]);
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
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

    $this->stub->expects($this->once())->method('send')->with($msg);

    $res = $this->call->playAudioAndCollect($collect, 'url-to-audio.mp3');
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
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

    $this->stub->expects($this->once())->method('send')->with($msg);

    $res = $this->call->playSilenceAndCollect($collect, 5);
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
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

    $this->stub->expects($this->once())->method('send')->with($msg);

    $res = $this->call->playTTSAndCollect($collect, ['text' => 'Welcome', 'gender' => 'male']);
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
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

    $this->stub->expects($this->once())->method('send')->with($msg);

    $res = $this->call->playMediaAndCollect(
      $collect,
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'Welcome', 'gender' => 'male']],
      ['type' => 'silence', 'params' => ['duration' => 5]]
    );
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
  }

  public function testStopPlayAndCollect(): void {
    $this->_setCallReady();
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play_and_collect.stop',
      'params' => [
        'call_id' => 'call-id',
        'node_id' => 'node-id',
        'control_id' => 'uuid'
      ]
    ]);

    $this->stub->expects($this->once())->method('send')->with($msg);

    $res = $this->call->stopPlayAndCollect('uuid');
    $this->assertInstanceOf('React\Promise\PromiseInterface', $res);
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

    $this->stub->expects($this->once())->method('send')->with($msg);

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

    $this->stub->expects($this->once())->method('send')->with($msg);

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

    $this->stub->expects($this->once())->method('send')->with($msg);

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
