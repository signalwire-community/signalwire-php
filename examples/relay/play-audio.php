<?php
error_reporting(E_ALL);

require dirname(__FILE__) . '/../../vendor/autoload.php';

$project = isset($_ENV['PROJECT']) ? $_ENV['PROJECT'] : '';
$token = isset($_ENV['TOKEN']) ? $_ENV['TOKEN'] : '';
if (empty($project) || empty($token)) {
  throw new \Exception('Set your SignalWire project and token before run the example!');
}

$client = new SignalWire\Relay\Client(array(
  "project" => $project,
  "token" => $token
));

$client->on('signalwire.ready', function($session) {

  $params = array('type' => 'phone', 'from' => '+1xxx', 'to' => '+1yyy');

  $session->calling->dial($params)->done(function($dialResult) {
    if (!$dialResult->isSuccessful()) {
      echo "\n Error dialing \n";
    }
    $call = $dialResult->getCall();

    $call->on('stateChange', function ($call) {
      echo PHP_EOL . $call->id . " state changed to " . $call->state . PHP_EOL;
    })
    ->on('play.stateChange', function ($call, $params) {
      echo PHP_EOL . $call->id . " GLOBAL play changed to " . $params->state . PHP_EOL;
    });

    $call->playAudio('https://cdn.signalwire.com/default-music/welcome.mp3');

  })->done(function ($response) {
    echo PHP_EOL . $response->message . PHP_EOL;
  });

});

$client->connect();
