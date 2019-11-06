<?php
error_reporting(E_ALL);

require dirname(__FILE__) . '/../../vendor/autoload.php';

use Generator as Coroutine;
use SignalWire\Relay\Consumer;
use SignalWire\Log;

class CustomConsumer extends Consumer {
  public $contexts = ['home', 'office'];

  public function setup() {
    $this->project = isset($_ENV['PROJECT']) ? $_ENV['PROJECT'] : '';
    $this->token = isset($_ENV['TOKEN']) ? $_ENV['TOKEN'] : '';
  }

  public function onIncomingCall($call): Coroutine {
    Log::info("Incoming call on context: {$call->context}, from: {$call->from} to: {$call->to}");
    yield $call->answer();
    $devices = [
      ['type' => 'phone', 'to' => '+1xxx']
    ];
    $result = yield $call->connect([
      'devices' => $devices
    ]);

    // For demonstration only: we disconnect the legs as soon as they have been connected.
    if ($result->isSuccessful()) {
      Log::info("Legs have been connected... now disconnect!");
      $disResult = yield $call->disconnect();
    }

    // Hangup the inbound leg, the remote leg is still active
    yield $call->hangup();
  }

  public function teardown(): Coroutine {
    yield;
    Log::info('Consumer teardown. Cleanup..');
  }
}

$consumer = new CustomConsumer();
$consumer->run();
