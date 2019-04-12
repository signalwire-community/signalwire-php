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

$client->on('signalwire.socket.open', function($session) {
  echo PHP_EOL . "signalwire.socket.open" . PHP_EOL;
});
$client->on('signalwire.socket.error', function($session) {
  echo PHP_EOL . "signalwire.socket.error" . PHP_EOL;
});
$client->on('signalwire.socket.close', function($session) {
  echo PHP_EOL . "signalwire.socket.close" . PHP_EOL;
});

$client->on('signalwire.ready', function($session) {
  echo PHP_EOL . "signalwire.ready" . PHP_EOL;
  sleep(5);
  $session->disconnect();
});

$client->on('signalwire.error', function($error) {
  echo PHP_EOL;
  echo "SignalWire Error:";
  echo $error->getMessage();
  echo PHP_EOL;
});

$client->connect();
