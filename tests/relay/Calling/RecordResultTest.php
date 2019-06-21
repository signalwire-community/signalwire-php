<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Calling\RecordResult;
use SignalWire\Messages\Execute;

class RelayCallingRecordResultTest extends TestCase
{
  public function testUpdateWithRecording(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","state":"recording","url":"record.mp3","duration":20.0,"size":123456788,"record":{"audio":{"format":"mp3","stereo":false,"direction":"listen"}}}');

    $result = new RecordResult($msg);

    $this->assertFalse($result->succeeded);
    $this->assertFalse($result->failed);
    $this->assertEquals($result->state, 'recording');
    $this->assertEquals($result->url, 'record.mp3');
  }

  public function testUpdateWithNoInput(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","state":"no_input","url":"record.mp3","duration":20.0,"size":123456788,"record":{"audio":{"format":"mp3","stereo":false,"direction":"both"}}}');

    $result = new RecordResult($msg);

    $this->assertFalse($result->succeeded);
    $this->assertTrue($result->failed);
    $this->assertEquals($result->state, 'no_input');
    $this->assertEquals($result->url, 'record.mp3');
  }

  public function testUpdateWithFinished(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","state":"finished","url":"record.mp3","duration":20.0,"size":123456788,"record":{"audio":{"format":"mp3","stereo":false,"direction":"listen"}}}');

    $result = new RecordResult($msg);

    $this->assertTrue($result->succeeded);
    $this->assertFalse($result->failed);
    $this->assertEquals($result->state, 'finished');
    $this->assertEquals($result->url, 'record.mp3');
  }
}
