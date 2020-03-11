<?php
namespace SignalWire\Relay;
use SignalWire\Messages\BaseMessage;
use SignalWire\Handler;
use SignalWire\Util\Events;
use SignalWire\Log;
use Ratchet\RFC6455\Messaging\Frame;
use Ratchet\Client\WebSocket;
use Ratchet\Client\Connector;

class Connection {
  const PING_INTERVAL = 5.0;

  private $_ws = null;
  private $_connected = false;
  private $_keepAliveTimer = null;
  private $_connectorTimer = null;

  public function __construct(Client $client) {
    $this->client = $client;
  }

  public function connect() {
    $host = \SignalWire\checkWebSocketHost($this->client->host);
    Log::debug("Connecting to: $host");

    $connector = new Connector($this->client->eventLoop);
    $connector($host)->done(
      function(WebSocket $webSocket) {
        $this->_ws = $webSocket;
        $this->_ws->on('message', function($msg) {
          Log::debug("RECV " . $msg->getPayload());
          $obj = json_decode($msg->getPayload());
          if (!is_object($obj) || !isset($obj->id)) {
            return;
          }
          if (Handler::trigger($obj->id, $obj) === false) {
            Handler::trigger(Events::SocketMessage, $obj, $this->client->uuid);
          }
        });

        $this->_ws->on('close', function($code = null, $reason = null) {
          $this->_connected = false;
          if ($this->_keepAliveTimer) {
            $this->client->eventLoop->cancelTimer($this->_keepAliveTimer);
          }
          $param = array('code' => $code, 'reason' => $reason);
          Handler::trigger(Events::SocketClose, $param, $this->client->uuid);
        });

        Handler::trigger(Events::SocketOpen, null, $this->client->uuid);

        $this->_keepAlive();
      },
      function(\Exception $error) {
        Handler::trigger(Events::SocketError, $error, $this->client->uuid);
      }
    );
  }

  public function close() {
    if (isset($this->_ws)) {
      $this->_ws->close();
      unset($this->_ws);
    } elseif ($this->_connectorTimer) {
      $this->client->eventLoop->cancelTimer($this->_connectorTimer);
    } else {
      $this->_connectorTimer = $this->client->eventLoop->addTimer(1, [$this, 'close']);
    }
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

    Log::debug("SEND {$msg->toJson()}");
    $this->_ws->send($msg->toJson());

    return $promise;
  }

  private function _keepAlive() {
    $this->_connected = true;

    $this->_ws->on('pong', function() {
      $this->_connected = true;
    });

    $this->_keepAliveTimer = $this->client->eventLoop->addPeriodicTimer(self::PING_INTERVAL, function () {
      if ($this->_connected) {
        $this->_connected = false;
        $this->_ws->send(new Frame('', true, Frame::OP_PING));
      } else {
        $this->_ws->close();
      }
    });
  }
}
