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

    $pdfDocument = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
    $result = yield $call->faxSend($pdfDocument);
    Log::info('isSuccessful: ' . $result->isSuccessful());
    Log::info('getDirection: ' . $result->getDirection());
    Log::info('getIdentity: ' . $result->getIdentity());
    Log::info('getRemoteIdentity: ' . $result->getRemoteIdentity());
    Log::info('getDocument: ' . $result->getDocument());
    Log::info('getPages: ' . $result->getPages());

    yield $call->hangup();
  }

  public function teardown(): Coroutine {
    yield;
    Log::info('Consumer teardown. Cleanup..');
  }
}

$consumer = new CustomConsumer();
$consumer->run();
