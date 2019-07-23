<?php
error_reporting(E_ALL);

require dirname(__FILE__) . '/../../vendor/autoload.php';

$project = isset($_ENV['PROJECT']) ? $_ENV['PROJECT'] : '';
$token = isset($_ENV['TOKEN']) ? $_ENV['TOKEN'] : '';
if (empty($project) || empty($token)) {
  throw new \Exception('Set your SignalWire project and token before running the example!');
}

$client = new SignalWire\Relay\Client([ 'project' => $project, 'token' => $token ]);

$client->on('signalwire.ready', function($client) {

  $params = array(
    'context' => 'test',
    'to_number' => '+1xxx',
    'from_number' => '+1yyy',
    'body' => 'Welcome at SignalWire!'
  );

  $client->messaging->send($params)->done(function($sendResult) {
    if (!$sendResult->isSuccessful()) {
      echo "\n Error sending message. \n";
    } else {
      echo "\n Message queued. \n";
    }
  });

});

$client->connect();
