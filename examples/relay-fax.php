<?php
error_reporting(E_ALL);

require dirname(__FILE__) . '/../vendor/autoload.php';

$project = isset($_ENV['PROJECT']) ? $_ENV['PROJECT'] : '';
$token = isset($_ENV['TOKEN']) ? $_ENV['TOKEN'] : '';
if (empty($project) || empty($token)) {
  throw new \Exception('Set your SignalWire project and token before running the example!');
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
    ->on('fax.stateChange', function ($call, $params) {
      echo PHP_EOL . $call->id . " Fax Notification " . PHP_EOL;
      print_r($params);
    });

    $call->faxSend('https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf')->done(function($result) {
      print PHP_EOL . 'isSuccessful: ' . $result->isSuccessful() . PHP_EOL;
      print PHP_EOL . 'getDirection: ' . $result->getDirection() . PHP_EOL;
      print PHP_EOL . 'getIdentity: ' . $result->getIdentity() . PHP_EOL;
      print PHP_EOL . 'getRemoteIdentity: ' . $result->getRemoteIdentity() . PHP_EOL;
      print PHP_EOL . 'getDocument: ' . $result->getDocument() . PHP_EOL;
      print PHP_EOL . 'getPages: ' . $result->getPages() . PHP_EOL;
    });

  });

});

$client->connect();
