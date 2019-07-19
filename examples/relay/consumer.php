<?php

require dirname(__FILE__) . '/../../vendor/autoload.php';

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

  public function teardown(): Coroutine {
    yield;
    echo "\n General cleanup here.. \n";
  }

  public function onTask($message): Coroutine {
    yield;
    echo "\n Task payload \n";
    print_r($message);
  }

  public function onIncomingCall($call): Coroutine {
    print "\n - onIncomingCall on context: {$call->context}, from: {$call->from} to: {$call->to} !\n";

    yield $call->answer();
    $action = yield $call->playAudioAsync('https://cdn.signalwire.com/default-music/welcome.mp3');

    $this->loop->addTimer(3, yield Consumer::callback(function() use ($call, $action) {
      yield $action->stop();
      yield $call->playTTS(['text' => 'Goodbye Sir!']);
      yield $call->hangup();
    }));
  }
}

$x = new CustomConsumer();
$x->run();
