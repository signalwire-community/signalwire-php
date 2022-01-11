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

  public function testFaxResponseLaMLMatch(): void {
    $response = new SignalWire\LaML\FaxResponse();
    $response->receive([
      'attr' => 'value',
      'key' => 'foo'
    ]);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Receive attr=\"value\" key=\"foo\"/></Response>\n");
  }

  public function testVoiceResponseLaMLMatch(): void {
    $response = new SignalWire\LaML\VoiceResponse();
    $response->connect([
      'field' => 'what',
    ]);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Connect field=\"what\"/></Response>\n");

    $response = new SignalWire\LaML\VoiceResponse();
    $response->dial('+12345', [
      'field' => 'what',
    ]);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Dial field=\"what\">+12345</Dial></Response>\n");

    $response = new SignalWire\LaML\VoiceResponse();
    $response->enqueue('Foo', [
      'field' => 'what',
    ]);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Enqueue field=\"what\">Foo</Enqueue></Response>\n");

    $response = new SignalWire\LaML\VoiceResponse();
    $response->gather([
      'field' => 'what',
    ]);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Gather field=\"what\"/></Response>\n");

    $response = new SignalWire\LaML\VoiceResponse();
    $response->hangup();
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Hangup/></Response>\n");

    $response = new SignalWire\LaML\VoiceResponse();
    $response->leave();
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Leave/></Response>\n");

    $response = new SignalWire\LaML\VoiceResponse();
    $response->pause([
      'field' => 'what',
    ]);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Pause field=\"what\"/></Response>\n");

    $response = new SignalWire\LaML\VoiceResponse();
    $response->play('some-url-here', [
      'field' => 'what',
    ]);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Play field=\"what\">some-url-here</Play></Response>\n");

    $response = new SignalWire\LaML\VoiceResponse();
    $response->queue('Name', [
      'field' => 'what',
    ]);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Queue field=\"what\">Name</Queue></Response>\n");

    $response = new SignalWire\LaML\VoiceResponse();
    $response->record([
      'field' => 'what',
    ]);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Record field=\"what\"/></Response>\n");

    $response = new SignalWire\LaML\VoiceResponse();
    $response->redirect('redirect-to',[
      'field' => 'what',
    ]);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Redirect field=\"what\">redirect-to</Redirect></Response>\n");

    $response = new SignalWire\LaML\VoiceResponse();
    $response->reject([
      'field' => 'what',
    ]);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Reject field=\"what\"/></Response>\n");

    $response = new SignalWire\LaML\VoiceResponse();
    $response->say('Hello!',[
      'field' => 'what',
    ]);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Say field=\"what\">Hello!</Say></Response>\n");

    $response = new SignalWire\LaML\VoiceResponse();
    $response->sms('body-here',[
      'field' => 'what',
    ]);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Sms field=\"what\">body-here</Sms></Response>\n");

    $response = new SignalWire\LaML\VoiceResponse();
    $response->pay([
      'field' => 'what',
    ]);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Pay field=\"what\"/></Response>\n");

    $response = new SignalWire\LaML\VoiceResponse();
    $response->prompt([
      'field' => 'what',
    ]);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Prompt field=\"what\"/></Response>\n");

    $response = new SignalWire\LaML\VoiceResponse();
    $response->start([
      'field' => 'what',
    ]);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Start field=\"what\"/></Response>\n");

    $response = new SignalWire\LaML\VoiceResponse();
    $response->stop();
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Stop/></Response>\n");


    $response = new SignalWire\LaML\VoiceResponse();
    $response->refer([
      'field' => 'what',
    ]);
    $this->assertEquals($response->__toString(), "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Refer field=\"what\"/></Response>\n");
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
