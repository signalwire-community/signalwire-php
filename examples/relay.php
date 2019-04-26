<?php
error_reporting(E_ALL);

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

  $options = array(
    'type' => 'phone',
    'from' => '+12014296600',
    'to' => '+12083660792'
  );
  $session->calling->newCall($options)->then(
    function($call) {
      $call->on('created', function ($call) {
        echo PHP_EOL . $call->id . " state changed from " . $call->prevState . " to " . $call->state . PHP_EOL;
      })
      ->on('ringing', function ($call) {
        echo PHP_EOL . $call->id . " state changed from " . $call->prevState . " to " . $call->state . PHP_EOL;
      })
      ->on('answered', function ($call) {
        echo PHP_EOL . $call->id . " state changed from " . $call->prevState . " to " . $call->state . PHP_EOL;
        // $call->playAudio('https://sample-videos.com/audio/mp3/crowd-cheering.mp3');
        $call->hangup();
      })
      ->on('ending', function ($call) {
        echo PHP_EOL . $call->id . " state changed from " . $call->prevState . " to " . $call->state . PHP_EOL;
      })
      ->on('ended', function ($call) {
        echo PHP_EOL . $call->id . " state changed from " . $call->prevState . " to " . $call->state . PHP_EOL;
      })
      ->begin()->then(
        function($response) {
          print_r($response);
        },
        function($error) {
          print_r($error);
        }
      );
    },
    function ($error) {
      print_r($error);
    }
  );


  // $session->calling->onInbound('home', function($call) {
  //   echo PHP_EOL . "onInbound - 1" . PHP_EOL;
  //   print_r($call);
  //   echo PHP_EOL . "onInbound - 2" . PHP_EOL;
  // });
});

$client->on('signalwire.error', function($error) {
  echo PHP_EOL;
  echo "SignalWire Error:";
  echo $error->getMessage();
  echo PHP_EOL;
});

$client->connect();
