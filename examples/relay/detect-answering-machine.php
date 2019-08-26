<?php
error_reporting(E_ALL);

require dirname(__FILE__) . '/../../vendor/autoload.php';

$project = isset($_ENV['PROJECT']) ? $_ENV['PROJECT'] : '';
$token = isset($_ENV['TOKEN']) ? $_ENV['TOKEN'] : '';
if (empty($project) || empty($token)) {
  throw new \Exception('Set your SignalWire project and token before run the example!');
}

$client = new SignalWire\Relay\Client([ 'project' => $project, 'token' => $token ]);

$client->on('signalwire.ready', function($client) {

  $params = array('type' => 'phone', 'from' => '+1xxx', 'to' => '+1yyy');

  $client->calling->dial($params)->done(function($dialResult) {
    if (!$dialResult->isSuccessful()) {
      echo "\n Error dialing \n";
      return;
    }
    $call = $dialResult->getCall();

    $call->on('detect.update', function ($call, $params) {
      print_r($params);
    });

    $call->amd()->done(function($result) use ($call) {
      print PHP_EOL . 'isSuccessful: ' . $result->isSuccessful() . PHP_EOL;
      print PHP_EOL . 'getType: ' . $result->getType() . PHP_EOL;
      print PHP_EOL . 'getResult: ' . $result->getResult() . PHP_EOL;

      $call->hangup();
    });

  });

});

$client->connect();
