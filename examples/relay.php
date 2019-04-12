<?php
require dirname(__FILE__) . '/../vendor/autoload.php';

$space_url = $_ENV['HOST'];
$project = $_ENV['PROJECT'];
$token = $_ENV['TOKEN'];
// if (empty($project) || empty($token)) {
//   throw new \Exception('Set your SignalWire project and token before run the example.');
// }

$client = new SignalWire\Relay\Client(array(
  "host" => $space_url,
  "project" => $project,
  "token" => $token
));

$client->on('signalwire.ready', function($session) {
  echo "Here bitch! signalwire.ready FTW";
});

$client->on('signalwire.error', function($error) {
  echo PHP_EOL;
  echo "SignalWire Error:";
  echo $error->getMessage();
  echo PHP_EOL;
});

$client->connect();
