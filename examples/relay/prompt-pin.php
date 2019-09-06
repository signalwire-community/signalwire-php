<?php

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
    $promptParams = [
      'type' => 'digits',
      'digits_max' => '4',
      'digits_terminators' => '#',
      'text' => 'Welcome at SignalWire. Please, enter your PIN and then # to proceed'
    ];
    $promptResult = yield $call->promptTTS($promptParams);
    $pin = $promptResult->getResult();
    Log::info('PIN: ' . $pin);
    if ($pin === '1234') {
      yield $call->playTTS(['text' => 'You entered the proper PIN. Thank you!']);
    } else {
      yield $call->playTTS(['text' => 'Unknown PIN.']);
    }
    yield $call->hangup();
  }

  public function teardown(): Coroutine {
    yield;
    Log::info('Consumer teardown. Cleanup..');
  }
}

$consumer = new CustomConsumer();
$consumer->run();
