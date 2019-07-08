<?php
error_reporting(E_ALL);

require dirname(__FILE__) . '/../vendor/autoload.php';
$loop = React\EventLoop\Factory::create();

$project = isset($_ENV['PROJECT']) ? $_ENV['PROJECT'] : '';
$token = isset($_ENV['TOKEN']) ? $_ENV['TOKEN'] : '';
if (empty($project) || empty($token)) {
  throw new \Exception('Set your SignalWire project and token before run the example!');
}

$client = new SignalWire\Relay\Client(array(
  "project" => $project,
  "token" => $token,
  "eventLoop" => $loop
));

$client->on('signalwire.socket.open', function($session) {
  echo PHP_EOL . "signalwire.socket.open" . PHP_EOL;
});
$client->on('signalwire.error', function($error) {
  echo PHP_EOL . $error->getMessage() . PHP_EOL;
});
$client->on('signalwire.socket.close', function($session) {
  echo PHP_EOL . "signalwire.socket.close" . PHP_EOL;
});

$client->on('signalwire.ready', function($session) use ($loop) {
  echo PHP_EOL . "signalwire.ready" . PHP_EOL;

  // Test onInbound
  $session->calling->onInbound('office', function($call) use ($session) {
    $call->on('answered', function ($call) {
      echo PHP_EOL . $call->id . " state changed from " . $call->prevState . " to " . $call->state . PHP_EOL;
      $collect = [ "initial_timeout" => 10, "digits" => [ "max" => 3, "digit_timeout" => 5 ] ];
      $params = [ "text" => "Welcome at SignalWire!" ];
      $call->playTTSAndCollect($collect, $params)->done(function($result) use ($call) {
        $params = [ "text" => "You pressed: " . (string)$result->params->digits ];
        $call->playTTS($params)->done(function($call) {
          echo PHP_EOL . "playTTS Done!" . PHP_EOL;
        }, function() {
          echo PHP_EOL . "playTTS ERROR???" . PHP_EOL;
        });
      }, function() {
        echo PHP_EOL . "playTTSAndCollect ERROR???" . PHP_EOL;
      });
      // $call->hangup();
    })
    ->on('ended', function ($call) use ($session) {
      echo PHP_EOL . "Disconnect..." . PHP_EOL;
      $session->disconnect();
    })
    ->answer();

  })->done();

  return;

  // Test newCall()
  $options = array(
    'type' => 'phone',
    'from' => '+1XXX',
    'to' => '+1YYY'
  );
  $session->calling->newCall($options)->then(
    function($call) {
      $call->on('stateChange', function ($call) {
        echo PHP_EOL . $call->id . " GLOBAL state changed to " . $call->state . PHP_EOL;
      })
      ->on('play.stateChange', function ($call, $params) {
        echo PHP_EOL . $call->id . " GLOBAL play changed to " . $params->state . PHP_EOL;
      })
      ->on('record.stateChange', function ($call, $params) {
        echo PHP_EOL . $call->id . " GLOBAL record changed to " . $params->state . PHP_EOL;
      })
      ->on('created', function ($call) {
        echo PHP_EOL . $call->id . " state changed from " . $call->prevState . " to " . $call->state . PHP_EOL;
      })
      ->on('ringing', function ($call) {
        echo PHP_EOL . $call->id . " state changed from " . $call->prevState . " to " . $call->state . PHP_EOL;
      })
      ->on('answered', function ($call) {
        echo PHP_EOL . $call->id . " state changed from " . $call->prevState . " to " . $call->state . PHP_EOL;
        $call->playAudio('https://sample-videos.com/audio/mp3/crowd-cheering.mp3');
        // $call->hangup();
      })
      ->on('ending', function ($call) {
        echo PHP_EOL . $call->id . " state changed from " . $call->prevState . " to " . $call->state . PHP_EOL;
      })
      ->on('ended', function ($call) {
        echo PHP_EOL . $call->id . " state changed from " . $call->prevState . " to " . $call->state . PHP_EOL;
      })
      ->begin()->then(
        function($response) {
          echo PHP_EOL . $response->message . PHP_EOL;
        },
        function($error) {
          echo PHP_EOL . $error->message . PHP_EOL;
        }
      );
    },
    function ($error) {
      print_r($error);
    }
  );
});

$client->connect();
