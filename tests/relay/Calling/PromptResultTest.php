<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Calling\PromptResult;
use SignalWire\Messages\Execute;

class RelayCallingPromptResultTest extends RelayCallingBaseActionCase
{
  // protected function setUp() { }

  public function testUpdateWithError(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","result":{"type":"error"}}');

    $result = new PromptResult($msg);

    $this->assertFalse($result->succeeded);
    $this->assertTrue($result->failed);
    $this->assertEquals($result->state, 'error');
    $this->assertNull($result->type);
    $this->assertNull($result->result);
  }

  public function testUpdateWithNoInput(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","result":{"type":"no_input"}}');

    $result = new PromptResult($msg);

    $this->assertFalse($result->succeeded);
    $this->assertTrue($result->failed);
    $this->assertEquals($result->state, 'no_input');
    $this->assertNull($result->type);
    $this->assertNull($result->result);
  }

  public function testUpdateWithNoMatch(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","result":{"type":"no_match"}}');

    $result = new PromptResult($msg);

    $this->assertFalse($result->succeeded);
    $this->assertTrue($result->failed);
    $this->assertEquals($result->state, 'no_match');
    $this->assertNull($result->type);
    $this->assertNull($result->result);
  }

  public function testUpdateWithDigits(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","result":{"type":"digit","params":{"digits":"12345","terminator":"#"}}}');

    $result = new PromptResult($msg);

    $this->assertTrue($result->succeeded);
    $this->assertFalse($result->failed);
    $this->assertEquals($result->state, 'successful');
    $this->assertEquals($result->type, 'digit');
    $this->assertEquals($result->result, $msg->result->params);
  }

  public function testUpdateWithSpeech(): void {
    $msg = json_decode('{"node_id":"node-id","call_id":"call-id","control_id":"control-id","result":{"type":"speech","params":{"text":"utterance heard","confidence":83.2}}}');

    $result = new PromptResult($msg);

    $this->assertTrue($result->succeeded);
    $this->assertFalse($result->failed);
    $this->assertEquals($result->state, 'successful');
    $this->assertEquals($result->type, 'speech');
    $this->assertEquals($result->result, $msg->result->params);
  }
}
