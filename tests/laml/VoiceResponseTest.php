<?php

use PHPUnit\Framework\TestCase;

class VoiceResponseTest extends TestCase
{
  public function testGeneratedVoiceResponseMatch(): void {
    $response = new SignalWire\LaML\VoiceResponse();
    $response->say('Hello');
    $response->play('https://cdn.signalwire.com/default-music/welcome.mp3', ['loop' => 5]);

    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Say>Hello</Say><Play loop=\"5\">https://cdn.signalwire.com/default-music/welcome.mp3</Play></Response>\n");
  }
}
