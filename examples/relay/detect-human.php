<?php
error_reporting(E_ALL);

require dirname(__FILE__) . '/../../vendor/autoload.php';

$project = isset($_ENV['PROJECT']) ? $_ENV['PROJECT'] : '';
$token = isset($_ENV['TOKEN']) ? $_ENV['TOKEN'] : '';
if (empty($project) || empty($token)) {
  throw new \Exception('Set your SignalWire project and token before run the example!');
}

$client = new SignalWire\Relay\Client([ 'host' => 'relay.swire.io', 'project' => $project, 'token' => $token ]);

$client->on('signalwire.ready', function($client) {

  $params = array('type' => 'phone', 'from' => '+12014296600', 'to' => '+12044000543');

  $client->calling->dial($params)->done(function($dialResult) {
    if (!$dialResult->isSuccessful()) {
      echo "\n Error dialing \n";
      return;
    }
    $call = $dialResult->getCall();

    $call->on('detect.update', function ($call, $params) {
      echo "\nDetect Update\n";
      print_r($params);
      echo "\nDetect Update\n";
    });

    $call->detectHuman()->done(function($result) use ($call) {
      if ($result->isSuccessful()) {
        print "\nIs a human!\n";
      }

      $call->hangup();
    });

  });

});

$client->connect();
