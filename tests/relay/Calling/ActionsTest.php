<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use SignalWire\Relay\Calling\Actions;
use SignalWire\Relay\Calling\Components;
use SignalWire\Messages\Execute;

class RelayCallingActionsTest extends RelayCallingBaseActionCase {

  protected function setUp() {
    parent::setUp();
    $this->_setCallReady();
    $this->_successResponse = \React\Promise\resolve(json_decode('{"result":{"code":"200","message":"message","control_id":"' . self::UUID . '"}}'));
    $this->_failResponse = \React\Promise\reject(json_decode('{"result":{"code":"400","message":"some error","control_id":"' . self::UUID . '"}}'));
  }

  public function testRecordActionStopWithSuccess(): void {
    $this->client->connection->expects($this->once())->method('send')->with($this->_recordStopMsg())->willReturn($this->_successResponse);
    $component = new Components\Record($this->call, ['audio' => 'blah']);
    $action = new Actions\RecordAction($component);
    $action->stop()->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\StopResult', $result);
      $this->assertTrue($result->successful);
      $this->assertEquals($result->code, '200');
    });
  }

  public function testRecordActionStopWithFail(): void {
    $this->client->connection->expects($this->once())->method('send')->with($this->_recordStopMsg())->willReturn($this->_failResponse);
    $component = new Components\Record($this->call, ['audio' => 'blah']);
    $action = new Actions\RecordAction($component);
    $action->stop()->done(function($result) use (&$action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\StopResult', $result);
      $this->assertFalse($result->successful);
      $this->assertEquals($result->code, '400');
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getState(), 'failed');
    });
  }

  public function testPlayActionStopWithSuccess(): void {
    $this->client->connection->expects($this->once())->method('send')->with($this->_playStopMsg())->willReturn($this->_successResponse);
    $component = new Components\Play($this->call, ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]);
    $action = new Actions\PlayAction($component);
    $action->stop()->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\StopResult', $result);
      $this->assertTrue($result->successful);
      $this->assertEquals($result->code, '200');
    });
  }

  public function testPlayActionStopWithFail(): void {
    $this->client->connection->expects($this->once())->method('send')->with($this->_playStopMsg())->willReturn($this->_failResponse);
    $component = new Components\Play($this->call, ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]);
    $action = new Actions\PlayAction($component);
    $action->stop()->done(function ($result) use (&$action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\StopResult', $result);
      $this->assertFalse($result->successful);
      $this->assertEquals($result->code, '400');
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getState(), 'failed');
    });
  }

  public function testPlayPauseWithSuccess(): void {
    $this->client->connection->expects($this->once())->method('send')->with($this->_playPauseMsg())->willReturn($this->_successResponse);
    $component = new Components\Play($this->call, ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]);
    $action = new Actions\PlayAction($component);
    $action->pause()->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PlayPauseResult', $result);
      $this->assertTrue($result->successful);
    });
  }

  public function testPlayPauseWithFail(): void {
    $this->client->connection->expects($this->once())->method('send')->with($this->_playPauseMsg())->willReturn($this->_failResponse);
    $component = new Components\Play($this->call, ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]);
    $action = new Actions\PlayAction($component);
    $action->pause()->done(function ($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PlayPauseResult', $result);
      $this->assertFalse($result->successful);
    });
  }

  public function testPlayResumeWithSuccess(): void {
    $this->client->connection->expects($this->once())->method('send')->with($this->_playResumeMsg())->willReturn($this->_successResponse);
    $component = new Components\Play($this->call, ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]);
    $action = new Actions\PlayAction($component);
    $action->resume()->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PlayResumeResult', $result);
      $this->assertTrue($result->successful);
    });
  }

  public function testPlayResumeWithFail(): void {
    $this->client->connection->expects($this->once())->method('send')->with($this->_playResumeMsg())->willReturn($this->_failResponse);
    $component = new Components\Play($this->call, ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]);
    $action = new Actions\PlayAction($component);
    $action->resume()->done(function ($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PlayResumeResult', $result);
      $this->assertFalse($result->successful);
    });
  }

  public function testPlayVolumeWithSuccess(): void {
    $this->client->connection->expects($this->once())->method('send')->with($this->_playVolumeMsg(4.1))->willReturn($this->_successResponse);
    $component = new Components\Play($this->call, ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]);
    $action = new Actions\PlayAction($component);
    $action->volume(4.1)->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PlayVolumeResult', $result);
      $this->assertTrue($result->successful);
    });
  }

  public function testPlayVolumeWithFail(): void {
    $this->client->connection->expects($this->once())->method('send')->with($this->_playVolumeMsg(4.1))->willReturn($this->_failResponse);
    $component = new Components\Play($this->call, ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]);
    $action = new Actions\PlayAction($component);
    $action->volume(4.1)->done(function ($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PlayVolumeResult', $result);
      $this->assertFalse($result->successful);
    });
  }

  public function testPromptActionStopWithSuccess(): void {
    $this->client->connection->expects($this->once())->method('send')->with($this->_promptStopMsg())->willReturn($this->_successResponse);
    $component = new Components\Prompt($this->call, ['digits' => 'blah'], ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]);
    $action = new Actions\PromptAction($component);
    $action->stop()->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\StopResult', $result);
      $this->assertTrue($result->successful);
      $this->assertEquals($result->code, '200');
    });
  }

  public function testPromptActionStopWithFail(): void {
    $this->client->connection->expects($this->once())->method('send')->with($this->_promptStopMsg())->willReturn($this->_failResponse);
    $component = new Components\Prompt($this->call, ['digits' => 'blah'], ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]);
    $action = new Actions\PromptAction($component);
    $action->stop()->done(function ($result) use (&$action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\StopResult', $result);
      $this->assertFalse($result->successful);
      $this->assertEquals($result->code, '400');
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getState(), 'failed');
    });
  }

  public function testPromptVolumeStopWithSuccess(): void {
    $this->client->connection->expects($this->once())->method('send')->with($this->_promptVolumeMsg(-4.5))->willReturn($this->_successResponse);
    $component = new Components\Prompt($this->call, ['digits' => 'blah'], ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]);
    $action = new Actions\PromptAction($component);
    $action->volume(-4.5)->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PromptVolumeResult', $result);
      $this->assertTrue($result->successful);
    });
  }

  public function testPromptVolumeStopWithFail(): void {
    $this->client->connection->expects($this->once())->method('send')->with($this->_promptVolumeMsg(-4.5))->willReturn($this->_failResponse);
    $component = new Components\Prompt($this->call, ['digits' => 'blah'], ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]);
    $action = new Actions\PromptAction($component);
    $action->volume(-4.5)->done(function ($result) use (&$action) {
      $this->assertInstanceOf('SignalWire\Relay\Calling\Results\PromptVolumeResult', $result);
      $this->assertFalse($result->successful);
    });
  }

  private function _recordStopMsg() {
    return new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'calling.record.stop',
      'params' => [ 'node_id' => 'node-id', 'call_id' => 'call-id', 'control_id' => self::UUID ]
    ]);
  }

  private function _playStopMsg() {
    return new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'calling.play.stop',
      'params' => [ 'node_id' => 'node-id', 'call_id' => 'call-id', 'control_id' => self::UUID ]
    ]);
  }

  private function _playPauseMsg() {
    return new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'calling.play.pause',
      'params' => [ 'node_id' => 'node-id', 'call_id' => 'call-id', 'control_id' => self::UUID ]
    ]);
  }

  private function _playResumeMsg() {
    return new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'calling.play.resume',
      'params' => [ 'node_id' => 'node-id', 'call_id' => 'call-id', 'control_id' => self::UUID ]
    ]);
  }

  private function _promptStopMsg() {
    return new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'calling.play_and_collect.stop',
      'params' => [ 'node_id' => 'node-id', 'call_id' => 'call-id', 'control_id' => self::UUID ]
    ]);
  }

  private function _playVolumeMsg($value) {
    return new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'calling.play.volume',
      'params' => [ 'node_id' => 'node-id', 'call_id' => 'call-id', 'control_id' => self::UUID, 'volume' => (float)$value ]
    ]);
  }

  private function _promptVolumeMsg($value) {
    return new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'calling.play_and_collect.volume',
      'params' => [ 'node_id' => 'node-id', 'call_id' => 'call-id', 'control_id' => self::UUID, 'volume' => (float)$value ]
    ]);
  }
}
