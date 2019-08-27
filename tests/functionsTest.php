<?php

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
  public function testReduceConnectParamsWithEmpty(): void {
    $this->assertEquals(\SignalWire\reduceConnectParams([], "222", 30), []);
  }

  public function testReduceConnectParamsWithOneDevice(): void {
    $final = [
      [
        [ "type" => "phone", "params" => [ "from_number" => "222", "to_number" => "777", "timeout" => 40 ] ]
      ]
    ];

    $input = [
      [ "type" => "phone", "to" => "777", "timeout" => 40 ]
    ];
    $this->assertEquals(\SignalWire\reduceConnectParams($input, "222", 30), $final);

    $input = [
      [
        [ "type" => "phone", "to" => "777", "timeout" => 40 ]
      ]
    ];
    $this->assertEquals(\SignalWire\reduceConnectParams($input, "222", 30), $final);
  }

  public function testReduceConnectParamsWithDevicesInSeries(): void {
    $final = [
      [
        [ "type" => "phone", "params" => [ "from_number" => "222", "to_number" => "777", "timeout" => 40 ] ]
      ],
      [
        [ "type" => "phone", "params" => [ "from_number" => "222", "to_number" => "888", "timeout" => 30 ] ]
      ]
    ];

    $input = [
      [ "type" => "phone", "to" => "777", "timeout" => 40 ],
      [ "type" => "phone", "to" => "888" ]
    ];
    $result = \SignalWire\reduceConnectParams($input, "222", 30);
    $this->assertEquals($result, $final);
  }

  public function testReduceConnectParamsWithDevicesInParallel(): void {
    $final = [
      [
        [ "type" => "phone", "params" => [ "from_number" => "222", "to_number" => "777", "timeout" => 40 ] ],
        [ "type" => "phone", "params" => [ "from_number" => "222", "to_number" => "888", "timeout" => 30 ] ]
      ]
    ];

    $input = [
      [
        [ "type" => "phone", "to" => "777", "timeout" => 40 ],
        [ "type" => "phone", "to" => "888" ]
      ]
    ];
    $result = \SignalWire\reduceConnectParams($input, "222", 30);
    $this->assertEquals($result, $final);
  }

  public function testReduceConnectParamsWithDevicesInSeriesAndParallel(): void {
    $final = [
      [
        [ "type" => "phone", "params" => [ "from_number" => "444", "to_number" => "333", "timeout" => 10 ] ]
      ],
      [
        [ "type" => "phone", "params" => [ "from_number" => "222", "to_number" => "777", "timeout" => 40 ] ],
        [ "type" => "phone", "params" => [ "from_number" => "222", "to_number" => "888", "timeout" => 30 ] ]
      ]
    ];

    $input = [
      [ "type" => "phone", "to" => "333", "from" => "444", "timeout" => 10 ],
      [
        [ "type" => "phone", "to" => "777", "timeout" => 40 ],
        [ "type" => "phone", "to" => "888" ]
      ]
    ];
    $result = \SignalWire\reduceConnectParams($input, "222", 30);
    $this->assertEquals($result, $final);

    $input = [
      [
        [ "type" => "phone", "to" => "333", "from" => "444", "timeout" => 10 ]
      ],
      [
        [ "type" => "phone", "to" => "777", "timeout" => 40 ],
        [ "type" => "phone", "to" => "888" ]
      ]
    ];
    $result = \SignalWire\reduceConnectParams($input, "222", 30);
    $this->assertEquals($result, $final);
  }

  public function testReduceConnectParamsWithDevicesInParallelAndSeries(): void {
    $final = [
      [
        [ "type" => "phone", "params" => [ "from_number" => "222", "to_number" => "777", "timeout" => 40 ] ],
        [ "type" => "phone", "params" => [ "from_number" => "222", "to_number" => "888", "timeout" => 30 ] ]
      ],
      [
        [ "type" => "phone", "params" => [ "from_number" => "444", "to_number" => "333", "timeout" => 10 ] ]
      ]
    ];

    $input = [
      [
        [ "type" => "phone", "to" => "777", "timeout" => 40 ],
        [ "type" => "phone", "to" => "888" ]
      ],
      [ "type" => "phone", "to" => "333", "from" => "444", "timeout" => 10 ]
    ];
    $result = \SignalWire\reduceConnectParams($input, "222", 30);
    $this->assertEquals($result, $final);

    $input = [
      [
        [ "type" => "phone", "to" => "777", "timeout" => 40 ],
        [ "type" => "phone", "to" => "888" ]
      ],
      [
        [ "type" => "phone", "to" => "333", "from" => "444", "timeout" => 10 ]
      ]
    ];
    $result = \SignalWire\reduceConnectParams($input, "222", 30);
    $this->assertEquals($result, $final);
  }

  public function testReduceConnectParamsWithComplexDevices(): void {
    $final = [
      [
        [ "type" => "phone", "params" => [ "from_number" => "222", "to_number" => "555", "timeout" => 12 ] ]
      ],
      [
        [ "type" => "phone", "params" => [ "from_number" => "222", "to_number" => "777", "timeout" => 30 ] ],
        [ "type" => "phone", "params" => [ "from_number" => "222", "to_number" => "888", "timeout" => 30 ] ]
      ],
      [
        [ "type" => "phone", "params" => [ "from_number" => "444", "to_number" => "333", "timeout" => 16 ] ]
      ],
      [
        [ "type" => "phone", "params" => [ "from_number" => "222", "to_number" => "777", "timeout" => 20 ] ],
        [ "type" => "phone", "params" => [ "from_number" => "222", "to_number" => "888", "timeout" => 7 ] ]
      ]
    ];

    $input = [
      [ "type" => "phone", "to" => "555", "from" => "222", "timeout" => 12 ],
      [
        [ "type" => "phone", "to" => "777" ],
        [ "type" => "phone", "to" => "888" ]
      ],
      [ "type" => "phone", "to" => "333", "from" => "444", "timeout" => 16 ],
      [
        [ "type" => "phone", "to" => "777", "timeout" => 20 ],
        [ "type" => "phone", "to" => "888", "timeout" => 7 ]
      ]
    ];
    $result = \SignalWire\reduceConnectParams($input, "222", 30);
    $this->assertEquals($result, $final);
  }

  public function testCheckWebSocketHost(): void {
    $this->assertEquals(\SignalWire\checkWebSocketHost('ws://test.com'), 'ws://test.com');
    $this->assertEquals(\SignalWire\checkWebSocketHost('example.signalwire.com'), 'wss://example.signalwire.com');
    $this->assertEquals(\SignalWire\checkWebSocketHost('ws://example.signalwire.com'), 'ws://example.signalwire.com');
    $this->assertEquals(\SignalWire\checkWebSocketHost('example.sw.com'), 'wss://example.sw.com');
  }

  public function testPrepareRecordParamsWithEmptyArray(): void {
    $expected = [ 'audio' => [] ];
    $input = [];
    $this->assertEquals(\SignalWire\prepareRecordParams($input), $expected);
  }

  public function testPrepareRecordParamsWithAudioKey(): void {
    $expected = [ 'audio' => [ 'beep' => true, 'format' => 'mp3', 'direction' => 'listen' ] ];
    $input = [
      'audio' => [
        'beep' => true,
        'format' => 'mp3',
        'direction' => 'listen'
      ]
    ];

    $this->assertEquals(\SignalWire\prepareRecordParams($input), $expected);
  }

  public function testPrepareRecordParamsWithFlattenedParams(): void {
    $expected = [ 'audio' => [ 'beep' => true, 'format' => 'mp3', 'direction' => 'listen' ] ];
    $input = [
      'beep' => true,
      'format' => 'mp3',
      'direction' => 'listen'
    ];

    $this->assertEquals(\SignalWire\prepareRecordParams($input), $expected);
  }

  public function testPrepareRecordParamsWithMixedParams(): void {
    $expected = [ 'audio' => [ 'beep' => true, 'format' => 'mp3', 'direction' => 'listen' ] ];
    $input = [
      'audio' => [
        'beep' => true
      ],
      'format' => 'mp3',
      'direction' => 'listen'
    ];

    $this->assertEquals(\SignalWire\prepareRecordParams($input), $expected);
  }

  public function testPreparePlayParamsWithEmptyArray(): void {
    // do nothing
    $this->assertEquals(\SignalWire\preparePlayParams([]), []);
  }

  public function testPreparePlayParamsWithTypeAndParamsKeys(): void {
    $expected = [
      [ 'type' => 'tts', 'params' => ['text' => 'hello', 'gender' => 'male'] ]
    ];
    $input = [
      [ 'type' => 'tts', 'params' => ['text' => 'hello', 'gender' => 'male'] ]
    ];

    $this->assertEquals(\SignalWire\preparePlayParams($input), $expected);
  }

  public function testPreparePlayParamsWithFlattenParams(): void {
    $expected = [
      [ 'type' => 'tts', 'params' => ['text' => 'hello', 'gender' => 'male'] ]
    ];
    $input = [
      [ 'type' => 'tts', 'text' => 'hello', 'gender' => 'male']
    ];

    $this->assertEquals(\SignalWire\preparePlayParams($input), $expected);
  }

  public function testPreparePlayParamsWithMixedParams(): void {
    $expected = [
      [ 'type' => 'audio', 'params' => ['url' => 'audio.mp3'] ],
      [ 'type' => 'silence', 'params' => ['duration' => 5] ],
      [ 'type' => 'tts', 'params' => ['text' => 'hello', 'gender' => 'male'] ]
    ];
    $input = [
      [ 'type' => 'audio', 'url' => 'audio.mp3'],
      [ 'type' => 'silence', 'duration' => 5],
      [ 'type' => 'tts', 'params' => ['text' => 'hello'], 'gender' => 'male' ]
    ];

    $this->assertEquals(\SignalWire\preparePlayParams($input), $expected);
  }
}
