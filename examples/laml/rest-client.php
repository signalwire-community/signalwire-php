<?php
require dirname(__FILE__) . '/../../vendor/autoload.php';

$space_url = "<your-space>.signalwire.com";
$project = "";
$token = "";
if (empty($project) || empty($token)) {
  throw new \Exception('Set your SignalWire project and token before run the example.');
}

$client = new SignalWire\Rest\Client($project, $token, array(
  "signalwireSpaceUrl" => $space_url
));

$calls = $client->calls->read();
echo "Total calls: " . count($calls) . chr(10);

$messages = $client->messages->read();
echo "Total messages: " . count($messages) . chr(10);
