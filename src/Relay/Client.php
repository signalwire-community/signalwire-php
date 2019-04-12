<?php
namespace SignalWire\Relay;
use SignalWire\Messages\BaseMessage;
use SignalWire\Messages\Connect;
use SignalWire\Util\Events;
use SignalWire\Util\BladeMethod;
use SignalWire\Handler;
use SignalWire\Log;

class Client {
  /**
   * Unique ID
   * @var String
   */
  public $uuid;

  /**
   * SignalWire Space Url
   * @var String
   */
  public $host;

  /**
   * SignalWire project
   * @var String
   */
  public $project;

  /**
   * SignalWire token
   * @var String
   */
  public $token;

  /**
   * SignalWire Session ID
   * @var String
   */
  public $sessionid;

  /**
   * SignalWire unique Node ID
   * @var String
   */
  public $nodeid;

  /**
   * WebSocket connection
   * @var SignalWire\Relay\Connection
   */
  protected $connection;

  /**
   * Check to auto reconnect when the socket goes down
   * @var Boolean
   */
  private $_autoReconnect = false;

  /**
   * Session idle state. If true we've to save every execute and dispatch them when a new connection will be active
   * @var Boolean
   */
  private $_idle = false;

  /**
   * Queue of the execute messages that must be sent when the session turns up
   * @var Boolean
   */
  private $_executeQueue = array();

  public function __construct(Array $options) {
    $this->host = $options['host'];
    $this->project = $options['project'];
    $this->token = $options['token'];

    $this->uuid = \SignalWire\Util\UUID::v4();
    $this->connection = new Connection($this);
    $this->_attachListeners();
  }

  public function connect() {
    $this->connection->connect();
  }

  public function disconnect() {
    $this->_idle = true;
    $this->_autoReconnect = false;
    if ($this->connection) {
      $this->connection->close();
    }
    unset($this->connection);
    $this->_executeQueue = array();
    $this->_detachListeners();
  }

  public function execute(BaseMessage $msg) {
    if ($this->_idle) {
      return new \React\Promise\Promise(function (callable $resolve) use ($msg) {
        array_push($this->_executeQueue, array('resolve' => $resolve, 'msg' => $msg));
      });
    }

    return $this->connection->send($msg);
  }

  public function _onSocketOpen() {
    $this->_idle = false;
    $bladeConnect = new Connect($this->project, $this->token, $this->sessionid);
    $this->execute($bladeConnect)->then(
      function($result) {
        $this->_autoReconnect = true;
        $this->sessionid = $result->sessionid;
        $this->nodeid = $result->nodeid;
        // if ($result->session_restored) { TODO: }

        $this->_emptyExecuteQueue();
        Handler::trigger(Events::Ready, $this, $this->uuid);
      }, function($error) {
        Handler::trigger(Events::Error, $error, $this->uuid);
      }
    );
  }

  public function _onSocketClose(Array $param = array()) {
    if ($this->_autoReconnect === false) {
      return;
    }
    sleep(1);
    $this->connect();
  }

  public function _onSocketError($error) {
    Handler::trigger(Events::Error, $error, $this->uuid);
  }

  public function _onSocketMessage($msg) {
    switch ($msg->method) {
      case BladeMethod::Broadcast:
        $protocol = $msg->params->protocol;
        $event = $msg->params->event;
        $channel = $msg->params->channel;
        $params = $msg->params->params;
        if (Handler::trigger($protocol, $params, $channel) === false) {
          Log::warning('Unknown broadcast message', [$protocol, $event, $channel, $params]);
        }
        break;
      case BladeMethod::Disconnect:
        $this->_idle = true;
        break;
    }
  }

  public function on(String $event, Callable $fn) {
    Handler::register($event, $fn, $this->uuid);
    return $this;
  }

  public function off(String $event, Callable $fn = null) {
    Handler::deRegister($event, $fn, $this->uuid);
    return $this;
  }

  private function _attachListeners() {
    $this->_detachListeners();
    $this->on(Events::SocketOpen, [$this, "_onSocketOpen"], $this->uuid);
    $this->on(Events::SocketClose, [$this, "_onSocketClose"], $this->uuid);
    $this->on(Events::SocketError, [$this, "_onSocketError"], $this->uuid);
    $this->on(Events::SocketMessage, [$this, "_onSocketMessage"], $this->uuid);
  }

  private function _detachListeners() {
    $this->off(Events::SocketOpen, [$this, "_onSocketOpen"], $this->uuid);
    $this->off(Events::SocketClose, [$this, "_onSocketClose"], $this->uuid);
    $this->off(Events::SocketError, [$this, "_onSocketError"], $this->uuid);
    $this->off(Events::SocketMessage, [$this, "_onSocketMessage"], $this->uuid);
  }

  private function _emptyExecuteQueue() {
    if ($this->_idle) {
      return;
    }
    foreach ($this->_executeQueue as $queue) {
      $promise = $this->execute($queue['msg']);
      call_user_func($queue['resolve'], $promise);
    }
  }
}
