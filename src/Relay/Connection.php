<?php
namespace SignalWire\Relay;
use SignalWire\Messages\BaseMessage;
use SignalWire\Handler;
use SignalWire\Log;
use SignalWire\Util\Events;

class Connection {
  private $_ws;

  public function __construct(Client $client) {
    $this->client = $client;
  }

  public function connect() {
    \Ratchet\Client\connect($this->client->host)->then(
      array($this, "onConnectSuccess"),
      array($this, "onConnectError")
    );
  }

  public function onConnectSuccess(\Ratchet\Client\WebSocket $webSocket) {
    $this->_ws = $webSocket;
    $webSocket->on('message', function($msg) {
      echo PHP_EOL . "RECV:" . PHP_EOL . $msg->getPayload() . PHP_EOL;
      // TODO: safe json_decode here
      $json = json_decode($msg->getPayload());
      Handler::trigger($json->id, $json);
    });

    Handler::trigger(Events::SocketOpen, null, $this->client->uuid);
  }

  public function onConnectError(\Exception $error) {
    Handler::trigger(Events::SocketError, $error, $this->client->uuid);
  }

  public function send(BaseMessage $msg) {
    $resolver = function (callable $resolve, callable $reject) use ($msg) {
      $callback = function($msg) use ($resolve, $reject) {
        isset($msg->error) ? $reject($msg->error) : $resolve($msg->result);
      };

      Handler::registerOnce($msg->id, $callback);
    };

    $canceller = function () {
      // Cancel/abort any running operations like network connections, streams etc.
      throw new Exception('Promise cancelled');
    };

    $promise = new \React\Promise\Promise($resolver, $canceller);

    echo PHP_EOL . "SEND:" . PHP_EOL . $msg->toJson(true) . PHP_EOL;
    $this->_ws->send($msg->toJson());

    return $promise;
  }
}
