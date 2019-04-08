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

$client->connect();
