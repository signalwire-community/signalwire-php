<?php
namespace SignalWire\Relay;

class Connection {
  private $wsClient;

  public function __construct(Client $client) {
    $this->client = $client;
  }

  public function connect() {
    \Ratchet\Client\connect($this->client->host)
      ->then(function(\Ratchet\Client\WebSocket $client) {
        $this->wsClient = $client;
        $client->on('message', function($msg) use ($client) {
          echo "Received: {$msg}\n";
        });
        $this->client->_onWsOpen();
      }, function(\Exception $error){
        echo "Not Connected with error";
        $this->client->_onWsError();
      });
  }

  public function send(String $str) {
    echo "Send this: " . PHP_EOL;
    echo $str;
    echo PHP_EOL;
    $this->wsClient->send($str);
  }
}
