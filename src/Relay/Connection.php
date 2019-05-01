<?php
namespace SignalWire\Relay;
use SignalWire\Messages\BaseMessage;
use SignalWire\Handler;
use SignalWire\Util\Events;

class Connection {
  private $_ws;

  public function __construct(Client $client) {
    $this->client = $client;
  }

  public function connect() {
    $host = \SignalWire\checkWebSocketHost($this->client->host);
    \Ratchet\Client\connect($host)->then(
      array($this, "onConnectSuccess"),
      array($this, "onConnectError")
    );
  }

  public function close() {
    if (isset($this->_ws)) {
      $this->_ws->close();
      unset($this->_ws);
    }
  }

  public function onConnectSuccess(\Ratchet\Client\WebSocket $webSocket) {
    $this->_ws = $webSocket;
    $uuid = $this->client->uuid;
    $webSocket->on('message', function($msg) use ($uuid) {
      // echo PHP_EOL . "RECV:" . PHP_EOL . $msg->getPayload() . PHP_EOL;
      $obj = json_decode($msg->getPayload());
      if (!is_object($obj) || !isset($obj->id)) {
        // Invalid message from the socket
        return;
      }
      if (Handler::trigger($obj->id, $obj) === false) {
        Handler::trigger(Events::SocketMessage, $obj, $uuid);
      }
    });

    $webSocket->on('close', function($code = null, $reason = null) use ($uuid) {
      $param = array('code' => $code, 'reason' => $reason);
      Handler::trigger(Events::SocketClose, $param, $uuid);
    });

    Handler::trigger(Events::SocketOpen, null, $uuid);
  }

  public function onConnectError(\Exception $error) {
    Handler::trigger(Events::SocketError, $error, $this->client->uuid);
  }

  public function send(BaseMessage $msg) {
    $promise = new \React\Promise\Promise(function (callable $resolve, callable $reject) use ($msg) {
      $callback = function($msg) use ($resolve, $reject) {
        if (isset($msg->error)) {
          return $reject($msg->error);
        }
        if (isset($msg->result->result->code) && $msg->result->result->code !== "200") {
          return $reject($msg->result);
        }
        $resolve($msg->result);
      };

      Handler::registerOnce($msg->id, $callback);
    });

    // echo PHP_EOL . "SEND:" . PHP_EOL . $msg->toJson(true) . PHP_EOL;
    $this->_ws->send($msg->toJson());

    return \React\Promise\Timer\timeout($promise, 10, \React\EventLoop\Factory::create());
  }
}
