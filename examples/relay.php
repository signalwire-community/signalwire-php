<?php
error_reporting(E_ALL);

require dirname(__FILE__) . '/../vendor/autoload.php';
$loop = React\EventLoop\Factory::create();

$space_url = isset($_ENV['HOST']) ? $_ENV['HOST'] : '';
$project = isset($_ENV['PROJECT']) ? $_ENV['PROJECT'] : '';
$token = isset($_ENV['TOKEN']) ? $_ENV['TOKEN'] : '';
if (empty($project) || empty($token)) {
  throw new \Exception('Set your SignalWire project and token before run the example!');
}

$client = new SignalWire\Relay\Client(array(
  "host" => $space_url,
  "project" => $project,
  "token" => $token,
  "eventLoop" => $loop
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

$client->on('signalwire.ready', function($session) use ($loop) {
  echo PHP_EOL . "signalwire.ready" . PHP_EOL;

  $loop->addTimer(3, function () {
    echo PHP_EOL . "I've been stopped for 3 seconds without block the process" . PHP_EOL;
  });

  // Test onInbound
  $session->calling->onInbound('home', function($call) use ($session) {
    $call->on('answered', function ($call) {
      echo PHP_EOL . $call->id . " state changed from " . $call->prevState . " to " . $call->state . PHP_EOL;
      $call->playAudio('https://sample-videos.com/audio/mp3/crowd-cheering.mp3');
      // $call->hangup();
    })
    ->on('ended', function ($call) use ($session) {
      echo PHP_EOL . "Disconnect..." . PHP_EOL;
      $session->disconnect();
    })
    ->answer();

  })->then(
    function($response) {
      echo PHP_EOL . $response->message . PHP_EOL;
    },
    function($error) {
      echo PHP_EOL . $error->message . PHP_EOL;
    }
  );

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

$client->on('signalwire.error', function($error) {
  echo PHP_EOL . $error->getMessage() . PHP_EOL;
});

$client->connect();
