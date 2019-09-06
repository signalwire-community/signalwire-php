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

  public function ready(): Coroutine {
    $params = ['type' => 'phone', 'from' => '+1xxx', 'to' => '+1yyy'];
    Log::info('Trying to dial: ' . $params['to']);
    $dialResult = yield $this->client->calling->dial($params);
    if (!$dialResult->isSuccessful()) {
      Log::warning('Outbound call failed or not answered.');
      return;
    }
    $call = $dialResult->getCall();
    Log::info('Start AMD..');
    $result = yield $call->amd();

    Log::info('isSuccessful: ' . $result->isSuccessful());
    Log::info('getType: ' . $result->getType());
    Log::info('getResult: ' . $result->getResult());

    yield $call->hangup();
  }

  public function teardown(): Coroutine {
    yield;
    Log::info('Consumer teardown. Cleanup..');
  }
}

$consumer = new CustomConsumer();
$consumer->run();
