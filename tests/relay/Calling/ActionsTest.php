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
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.record.stop',
      'params' => [
        'node_id' => 'node-id',
        'call_id' => 'call-id',
        'control_id' => self::UUID
      ]
    ]);
    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $component = new Components\Record($this->call, ['audio' => 'blah']);
    $action = new Actions\RecordAction($component);

    $action->stop()->done(function($result) {
      $this->assertEquals($result->code, '200');
    });
  }

  public function testRecordActionStopWithFail(): void {
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.record.stop',
      'params' => [
        'node_id' => 'node-id',
        'call_id' => 'call-id',
        'control_id' => self::UUID
      ]
    ]);
    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_failResponse);

    $component = new Components\Record($this->call, ['audio' => 'blah']);
    $action = new Actions\RecordAction($component);

    $action->stop()->done(function($result) use (&$action) {
      $this->assertEquals($result->code, '400');
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getState(), 'failed');
    });
  }

  public function testPlayActionStopWithSuccess(): void {
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play.stop',
      'params' => [
        'node_id' => 'node-id',
        'call_id' => 'call-id',
        'control_id' => self::UUID
      ]
    ]);
    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $component = new Components\Play($this->call, ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]);
    $action = new Actions\PlayAction($component);

    $action->stop()->done(function($result) {
      $this->assertEquals($result->code, '200');
    });
  }

  public function testPlayActionStopWithFail(): void {
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play.stop',
      'params' => [
        'node_id' => 'node-id',
        'call_id' => 'call-id',
        'control_id' => self::UUID
      ]
    ]);
    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_failResponse);

    $component = new Components\Play($this->call, ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]);
    $action = new Actions\PlayAction($component);

    $action->stop()->done(function ($result) use (&$action) {
      $this->assertEquals($result->code, '400');
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getState(), 'failed');
    });
  }

  public function testPromptActionStopWithSuccess(): void {
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play_and_collect.stop',
      'params' => [
        'node_id' => 'node-id',
        'call_id' => 'call-id',
        'control_id' => self::UUID
      ]
    ]);
    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_successResponse);

    $component = new Components\Prompt($this->call, ['digits' => 'blah'], ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]);
    $action = new Actions\PromptAction($component);

    $action->stop()->done(function($result) {
      $this->assertEquals($result->code, '200');
    });
  }

  public function testPromptActionStopWithFail(): void {
    $msg = new Execute([
      'protocol' => 'signalwire_calling_proto',
      'method' => 'call.play_and_collect.stop',
      'params' => [
        'node_id' => 'node-id',
        'call_id' => 'call-id',
        'control_id' => self::UUID
      ]
    ]);
    $this->client->connection->expects($this->once())->method('send')->with($msg)->willReturn($this->_failResponse);

    $component = new Components\Prompt($this->call, ['digits' => 'blah'], ['type' => 'audio', 'params' => ['url' => 'url-to-audio.mp3']]);
    $action = new Actions\PromptAction($component);

    $action->stop()->done(function ($result) use (&$action) {
      $this->assertEquals($result->code, '400');
      $this->assertTrue($action->isCompleted());
      $this->assertEquals($action->getState(), 'failed');
    });
  }
}
