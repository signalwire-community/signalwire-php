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

  public function ready(): Coroutine {
    $params = ['type' => 'phone', 'from' => '+1xxx', 'to' => '+1yyy'];
    Log::info('Trying to dial: ' . $params['to']);
    $dialResult = yield $this->client->calling->dial($params);
    if (!$dialResult->isSuccessful()) {
      echo "\n Error dialing \n";
      return;
    }
    $call = $dialResult->getCall();
    Log::info('Sending digits..');
    $result = yield $call->sendDigits('1w2w3w4w5w6');
    if ($result->isSuccessful()) {
      Log::error('Digits sent successfully!');
    } else {
      Log::error('Error sending digits!');
    }
    yield $call->hangup();
  }

  public function teardown(): Coroutine {
    yield;
    echo "\n General cleanup here.. \n";
  }
}

$x = new CustomConsumer();
$x->run();
