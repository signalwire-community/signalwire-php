<?php

use PHPUnit\Framework\TestCase;

class LaMLTest extends TestCase
{
  public function testGeneratedLaMLMatch(): void {
    $response = new SignalWire\LaML();
    $response->say("Hey!");
    $response->play("https://ccrma.stanford.edu/~jos/mp3/gtr-nylon22.mp3", array("loop" => 5));
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Say>Hey!</Say><Play loop=\"5\">https://ccrma.stanford.edu/~jos/mp3/gtr-nylon22.mp3</Play></Response>\n");
  }

  public function testMessageResponseLaMLMatch(): void {
    $response = new SignalWire\LaML\MessageResponse();
    $response->message("Hello World", ['attr' => 'value']);
    $response->redirect("foo", ['method' => 'GET']);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Message attr=\"value\">Hello World</Message><Redirect method=\"GET\">foo</Redirect></Response>\n");
  }

  public function testMessagingResponseLaMLMatch(): void {
    $response = new SignalWire\LaML\MessagingResponse();
    $response->message("Hello World", ['attr' => 'value']);
    $response->redirect("foo", ['method' => 'GET']);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Message attr=\"value\">Hello World</Message><Redirect method=\"GET\">foo</Redirect></Response>\n");
  }
}
