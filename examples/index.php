<?php
require dirname(__FILE__) . '/../vendor/autoload.php';

if (!isset($_ENV['SIGNALWIRE_API_PROJECT']) || !isset($_ENV['SIGNALWIRE_API_TOKEN'])) {
  throw new \Exception('Missing SIGNALWIRE_API_PROJECT or SIGNALWIRE_API_TOKEN environment variables.');
}

$client = new SignalWire\Rest\Client($_ENV['SIGNALWIRE_API_PROJECT'], $_ENV['SIGNALWIRE_API_TOKEN']);

$calls = $client->calls->read();
echo "Total calls: " . count($calls) . chr(10);

$messages = $client->messages->read();
echo "Total messages: " . count($messages) . chr(10);