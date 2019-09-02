<?php
error_reporting(E_ALL);

require dirname(__FILE__) . '/../../vendor/autoload.php';

use Generator as Coroutine;
use SignalWire\Relay\Consumer;
use SignalWire\Log;

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

  public function onIncomingCall($call): Coroutine {
    print "\n - onIncomingCall on context: {$call->context}, from: {$call->from} to: {$call->to} !\n";

    yield $call->answer();

    $call->on('detect.update', function ($call, $params) {
      print_r($params);
    });

    $result = yield $call->detectDigit(['digits' => '123']);
    Log::info('isSuccessful: ' . $result->isSuccessful());
    Log::info('getResult: ' . $result->getResult());
    yield $call->hangup();
  }
}

$x = new CustomConsumer();
$x->run();
