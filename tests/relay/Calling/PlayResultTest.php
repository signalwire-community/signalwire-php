<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Calling\PlayResult;
use SignalWire\Messages\Execute;

class RelayCallingPlayResultTest extends TestCase
{
  // protected function setUp() { }

  public function testUpdateWithPlaying(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","state":"playing"}');

    $result = new PlayResult($msg);

    $this->assertFalse($result->succeeded);
    $this->assertFalse($result->failed);
    $this->assertEquals($result->state, 'playing');
  }

  public function testUpdateWithNoInput(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","state":"error"}');

    $result = new PlayResult($msg);

    $this->assertFalse($result->succeeded);
    $this->assertTrue($result->failed);
    $this->assertEquals($result->state, 'error');
  }

  public function testUpdateWithFinished(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","state":"finished"}');

    $result = new PlayResult($msg);

    $this->assertTrue($result->succeeded);
    $this->assertFalse($result->failed);
    $this->assertEquals($result->state, 'finished');
  }
}
