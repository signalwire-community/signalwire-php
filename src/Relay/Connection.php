<?php
namespace SignalWire\Relay;

class Connection {
  private $wsClient;

  public function __construct(Client $client) {
    $this->client = $client;
  }

  public function connect() {
    \Ratchet\Client\connect($this->client->host)->then(
      array($this, "onConnectSuccess"),
      array($this, "onConnectError")
    );
  }

  public function onConnectSuccess(\Ratchet\Client\WebSocket $client) {
    $this->wsClient = $client;
    $client->on('message', function($msg) {
      // TODO: safe json_decode here
      $json = json_decode($msg->getPayload());
      \SignalWire\Handler::trigger("wsMessage", $json, $json->id);
    });

    $this->client->_onSocketOpen();
  }

  public function onConnectError(\Exception $error) {
    \SignalWire\Log::warning('Connect error...');
    $this->client->_onSocketError();
  }

  public function send(\SignalWire\Messages\BaseMessage $msg) {
    $resolver = function (callable $resolve, callable $reject) use ($msg) {
      $callback = function($msg) use ($resolve, $reject) {
        \SignalWire\Log::warning('Handle response:', [$msg]);
        isset($msg->error) ? $reject($msg->error) : $resolve($msg->result);
      };

      // TODO: use registerOnce instead of register!
      \SignalWire\Handler::register("wsMessage", $callback, $msg->id);
    };

    $canceller = function () {
      // Cancel/abort any running operations like network connections, streams etc.
      throw new Exception('Promise cancelled');
    };

    $promise = new \React\Promise\Promise($resolver, $canceller);

    \SignalWire\Log::warning($msg->toJson(true));
    $this->wsClient->send($msg->toJson());

    return $promise;
  }
}
