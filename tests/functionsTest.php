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
    $this->assertEquals(\SignalWire\preparePlayParams([]), [[], 0]);
  }

  public function testPreparePlayParamsWithTypeAndParamsKeys(): void {
    $expected = [
      [ 'type' => 'tts', 'params' => ['text' => 'hello', 'gender' => 'male'] ]
    ];
    $input = [
      [ 'type' => 'tts', 'params' => ['text' => 'hello', 'gender' => 'male'] ]
    ];

    $this->assertEquals(\SignalWire\preparePlayParams($input), [$expected, 0]);
  }

  public function testPreparePlayParamsWithFlattenParams(): void {
    $expected = [
      [ 'type' => 'tts', 'params' => ['text' => 'hello', 'gender' => 'male'] ]
    ];
    $input = [
      [ 'type' => 'tts', 'text' => 'hello', 'gender' => 'male']
    ];

    $this->assertEquals(\SignalWire\preparePlayParams($input), [$expected, 0]);
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

    $this->assertEquals(\SignalWire\preparePlayParams($input), [$expected, 0]);
  }

  public function testPreparePromptParamsWithEmptyArray(): void {
    $expected = [[], [], 0];
    $input = [];
    $this->assertEquals(\SignalWire\preparePromptParams($input), $expected);
  }

  public function testPreparePromptParamsWithRequiredParamsOnly(): void {
    $collectExpected = [
      'initial_timeout' => 5,
      'digits' => (object)[],
      'speech' => (object)[]
    ];
    $playExpected = [
      ['type' => 'audio', 'params' => ['url' => 'audio.mp3']],
      ['type' => 'tts', 'params' => ['text' => 'hello', 'gender' => 'male']]
    ];
    $params = [
      'initial_timeout' => 5,
      'type' => 'both',
      'media' => [
        ['type' => 'audio', 'url' => 'audio.mp3'],
        ['type' => 'tts', 'text' => 'hello', 'gender' => 'male']
      ]
    ];
    list($collect, $play) = \SignalWire\preparePromptParams($params);
    $this->assertEquals($collect, $collectExpected);
    $this->assertEquals($play, $playExpected);
  }

  public function testPreparePromptParamsWithDigitsKey(): void {
    $collectExpected = [
      'initial_timeout' => 5,
      'digits' => [
        'max' => 3, 'digit_timeout' => 2, 'terminators' => '#'
      ]
    ];
    $playExpected = [
      [ 'type' => 'audio', 'params' => ['url' => 'audio.mp3'] ],
      [ 'type' => 'silence', 'params' => ['duration' => 5] ],
      [ 'type' => 'tts', 'params' => ['text' => 'hello', 'gender' => 'male'] ]
    ];
    list($collect, $play) = \SignalWire\preparePromptParams($collectExpected, $playExpected);
    $this->assertEquals($collect, $collectExpected);
    $this->assertEquals($play, $playExpected);
  }

  public function testPreparePromptParamsWithSpeechKeyAndFlattenedMedia(): void {
    $collectExpected = [
      'initial_timeout' => 5,
      'speech' => [
        'end_silence_timeout' => 3
      ]
    ];
    $playExpected = [
      [ 'type' => 'audio', 'params' => ['url' => 'audio.mp3'] ],
      [ 'type' => 'silence', 'params' => ['duration' => 5] ],
      [ 'type' => 'tts', 'params' => ['text' => 'hello', 'gender' => 'male'] ]
    ];
    $flattenedMedia = [
      ['type' => 'audio', 'url' => 'audio.mp3'],
      ['type' => 'silence', 'duration' => 5],
      ['type' => 'tts', 'text' => 'hello', 'gender' => 'male']
    ];
    list($collect, $play) = \SignalWire\preparePromptParams($collectExpected, $flattenedMedia);
    $this->assertEquals($collect, $collectExpected);
    $this->assertEquals($play, $playExpected);
  }

  public function testPreparePromptParamsWithFlattenedParams(): void {
    $collectExpected = [
      'initial_timeout' => 5,
      'digits' => [
        'max' => 3, 'digit_timeout' => 2, 'terminators' => '#'
      ],
      'speech' => [
        'end_silence_timeout' => 3,
        'speech_timeout' => 3
      ]
    ];
    $playExpected = [
      [ 'type' => 'audio', 'params' => ['url' => 'audio.mp3'] ],
      [ 'type' => 'silence', 'params' => ['duration' => 5] ],
      [ 'type' => 'tts', 'params' => ['text' => 'hello', 'gender' => 'male'] ]
    ];
    $params = [
      'initial_timeout' => 5,
      'digits_max' => 3,
      'digits_timeout' => 2,
      'digits_terminators' => '#',
      'end_silence_timeout' => 3,
      'speech_timeout' => 3,
      'NOT_EXISTS' => 'this will be ignored',
      'media' => [
        [ 'type' => 'audio', 'url' => 'audio.mp3'],
        [ 'type' => 'silence', 'duration' => 5],
        [ 'type' => 'tts', 'text' => 'hello', 'gender' => 'male']
      ]
    ];
    list($collect, $play) = \SignalWire\preparePromptParams($params);
    $this->assertEquals($collect, $collectExpected);
    $this->assertEquals($play, $playExpected);
  }

  public function testPreparePromptParamsWithoutMedia(): void {
    $collectExpected = [
      'initial_timeout' => 5,
      'speech' => [
        'end_silence_timeout' => 3
      ]
    ];
    $playExpected = [];

    $params = [
      'initial_timeout' => 5,
      'end_silence_timeout' => 3
    ];
    list($collect, $play) = \SignalWire\preparePromptParams($params);
    $this->assertEquals($collect, $collectExpected);
    $this->assertEquals($play, $playExpected);
  }

  public function testPreparePromptAudioParams(): void {
    $expected = [
      'initial_timeout' => 5,
      'media' => [
        [ 'type' => 'audio', 'params' => ['url' => 'audio.mp3'] ]
      ]
    ];
    $this->assertEquals(\SignalWire\preparePromptAudioParams(['initial_timeout' => 5, 'url' => 'audio.mp3']), $expected);
    $this->assertEquals(\SignalWire\preparePromptAudioParams(['initial_timeout' => 5], 'audio.mp3'), $expected);
  }

  public function testPreparePromptTTSParams(): void {
    $expected = [
      'initial_timeout' => 5,
      'media' => [
        [ 'type' => 'tts', 'params' => ['text' => 'welcome', 'gender' => 'male'] ]
      ]
    ];
    $this->assertEquals(\SignalWire\preparePromptTTSParams(['initial_timeout' => 5, 'text' => 'welcome', 'gender' => 'male']), $expected);
    $this->assertEquals(\SignalWire\preparePromptTTSParams(['initial_timeout' => 5], ['text' => 'welcome', 'gender' => 'male']), $expected);
  }

  public function testPrepareDetectParamsWithRequiredParamsOnly(): void {
    $expected = [
      'type' => 'fax', 'params' => []
    ];
    $input = [
      'type' => 'fax'
    ];
    list($detect, $timeout, $waitForBeep) = \SignalWire\prepareDetectParams($input);
    $this->assertEquals($detect, $expected);
    $this->assertNull($timeout);
    $this->assertFalse($waitForBeep);
  }

  public function testPrepareDetectParamsWithWaitForBeep(): void {
    $expected = [
      'type' => 'machine', 'params' => []
    ];
    $input = [
      'type' => 'machine', 'timeout' => 20, 'wait_for_beep' => true
    ];
    list($detect, $timeout, $waitForBeep) = \SignalWire\prepareDetectParams($input);
    $this->assertEquals($detect, $expected);
    $this->assertEquals($timeout, 20);
    $this->assertTrue($waitForBeep);
  }

  public function testPrepareDetectParamsWithDigits(): void {
    $expected = [
      'type' => 'digit', 'params' => ['digits' => '1234']
    ];
    $input = [
      'type' => 'digit', 'digits' => '1234'
    ];
    list($detect, $timeout) = \SignalWire\prepareDetectParams($input);
    $this->assertEquals($detect, $expected);
    $this->assertNull($timeout);
  }

  public function testPrepareDetectFaxParamsAndEventsWithEmptyArray(): void {
    $expected = [
      'type' => 'fax', 'params' => []
    ];
    $input = ['type' => 'fax'];
    list($detect, $timeout, $events) = \SignalWire\prepareDetectFaxParamsAndEvents($input);
    $this->assertEquals($detect, $expected);
    $this->assertNull($timeout);
    $this->assertEquals($events, ['CED', 'CNG']);
  }

  public function testPrepareDetectFaxParamsAndEventsWithTone(): void {
    $expected = [
      'type' => 'fax', 'params' => ['tone' => 'CED']
    ];
    $input = [
      'type' => 'fax', 'tone' => 'CED'
    ];
    list($detect, $timeout, $events) = \SignalWire\prepareDetectFaxParamsAndEvents($input);
    $this->assertEquals($detect, $expected);
    $this->assertNull($timeout);
    $this->assertEquals($events, ['CED']);
  }

  public function testPrepareTapParamsWithBothTapAndDevice(): void {
    $expectedTap = [ 'type' => 'audio', 'params' => [ 'direction' => 'listen' ] ];
    $expectedDevice = [ 'type' => 'rtp', 'params' => [ 'addr' => '127.0.0.1', 'port' => 1234 ] ];

    $tapParams = [ 'type' => 'audio', 'direction' => 'listen' ];
    $deviceParams = [ 'type' => 'rtp', 'addr' => '127.0.0.1', 'port' => 1234 ];

    list($tap, $device) = \SignalWire\prepareTapParams($tapParams, $deviceParams);

    $this->assertEquals($tap, $expectedTap);
    $this->assertEquals($device, $expectedDevice);
  }

  public function testPrepareTapParamsWithFlattenedParams(): void {
    $expectedTap = [ 'type' => 'audio', 'params' => [ 'direction' => 'listen' ] ];
    $expectedDevice = [ 'type' => 'rtp', 'params' => [ 'addr' => '127.0.0.1', 'port' => 1234 ] ];

    $params = [
      'audio_direction' => 'listen',
      'target_type' => 'rtp',
      'target_addr' => '127.0.0.1',
      'target_port' => 1234
    ];

    list($tap, $device) = \SignalWire\prepareTapParams($params);

    $this->assertEquals($tap, $expectedTap);
    $this->assertEquals($device, $expectedDevice);
  }

  public function testPrepareTapParamsWithoutDirection(): void {
    $expectedTap = [ 'type' => 'audio', 'params' => [] ];
    $expectedDevice = [ 'type' => 'rtp', 'params' => [ 'addr' => '127.0.0.1', 'port' => 1234, 'codec' => 'OPUS' ] ];

    $params = [
      'target_type' => 'rtp',
      'target_addr' => '127.0.0.1',
      'target_port' => 1234,
      'codec' => 'OPUS'
    ];

    list($tap, $device) = \SignalWire\prepareTapParams($params);

    $this->assertEquals($tap, $expectedTap);
    $this->assertEquals($device, $expectedDevice);
  }
}
