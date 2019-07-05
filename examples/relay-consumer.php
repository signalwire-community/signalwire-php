<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

use Generator as Coroutine;
use SignalWire\Relay\Consumer;

class CustomConsumer extends Consumer {
  public $project;
  public $token;
  public $contexts = ['home', 'office'];

  public function setup() {
    $this->project = isset($_ENV['PROJECT']) ? $_ENV['PROJECT'] : '';
    $this->token = isset($_ENV['TOKEN']) ? $_ENV['TOKEN'] : '';
  }

  public function ready(): Coroutine {
    echo PHP_EOL . ' Consumer is ready! ' . PHP_EOL;
    yield;
  }

  public function onIncomingCall($call): Coroutine {
    print "\n - onIncomingCall on context: {$call->context}, from: {$call->from} to: {$call->to} !\n";

    yield $call->answer();
    yield $call->playAudio('https://cdn.signalwire.com/default-music/welcome.mp3');
    yield $call->playTTS([ 'text' => 'Goodbye Sir!' ]);
    yield $call->hangup();
  }
}

$x = new CustomConsumer();
$x->run();
