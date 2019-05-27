<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

use Generator as Coroutine;

class CustomConsumer extends \SignalWire\Relay\Consumer {
  public $spaceUrl;
  public $project;
  public $token;
  public $contexts = ['home', 'office'];

  function __construct() {
    $this->spaceUrl = isset($_ENV['HOST']) ? $_ENV['HOST'] : '';
    $this->project = isset($_ENV['PROJECT']) ? $_ENV['PROJECT'] : '';
    $this->token = isset($_ENV['TOKEN']) ? $_ENV['TOKEN'] : '';

    parent::__construct();
  }

  public function onIncomingCall($call): Coroutine {
    // yield;
    $this->_dump();
    print "\n - onIncomingCall on context: {$call->context}, from: {$call->from} to: {$call->to} !\n";
    // $call->on("answered", function($call) {
    // });

    yield $call->answer();
    $action = yield $call->playAudio('https://cdn.signalwire.com/default-music/welcome.mp3');
    $this->loop->addTimer(5, function () use ($action) {
      $action->stop();
    });
    $this->loop->addTimer(7, function () use ($call) {
      $call->hangup();
      $this->_dump();
    });
  }

  private function _dump() {
    echo sprintf("\nMemory: %s|%s\n", memory_get_usage(), memory_get_peak_usage());
  }

  public function setup(): Coroutine {
    $this->_dump();
    yield;
  }

  // public function tearDown(): Coroutine {
  //   print "TearDown!\n";
  // }
}

$x = new CustomConsumer();
$x->run();
