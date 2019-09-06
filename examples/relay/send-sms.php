<?php

/// See handle-messages.php to see how to handle inbound SMS/MMS within the Consumer!

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

  // Once the Consumer is ready send an SMS
  public function ready(): Coroutine {
    $params = [
      'context' => 'office',
      'from' => '+1xxx',
      'to' => '+1yyy',
      'body' => 'Welcome at SignalWire!'
    ];
    Log::info('Sending SMS..');
    $result = yield $this->client->messaging->send($params);
    if ($result->isSuccessful()) {
      Log::info('SMS queued successfully!');
    } else {
      Log::warning('Error sending SMS!');
    }
  }

  // Keep track of your SMS state changes
  public function onMessageStateChange($message): Coroutine {
    yield;
    Log::info("Message {$message->id} state: {$message->state}.");
    print_r($message);
  }

  public function teardown(): Coroutine {
    yield;
    Log::info('Consumer teardown. Cleanup..');
  }
}

$consumer = new CustomConsumer();
$consumer->run();
