<?php
namespace SignalWire\Relay;
use Ramsey\Uuid\Uuid;
use SignalWire\Messages\BaseMessage;
use SignalWire\Messages\Connect;
use SignalWire\Messages\Subscription;
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
  public $connection;

  /**
   * EventLoop to use in WebSocket client
   * @var React\EventLoop
   */
  public $eventLoop = null;

  /**
   * Relay Calling service
   * @var SignalWire\Relay\Service\Calling
   */
  protected $_calling = null;

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

  /**
   * Hash with proto+channel this session is subscribed to
   * @var Boolean
   */
  private $_subscriptions = array();

  public function __construct(Array $options) {
    $this->host = $options['host'];
    $this->project = $options['project'];
    $this->token = $options['token'];
    if (isset($options['eventLoop']) && $options['eventLoop'] instanceof \React\EventLoop\LoopInterface) {
      $this->eventLoop = $options['eventLoop'];
    }

    $this->uuid = Uuid::uuid4()->toString();
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
        Log::info("Session Ready!");
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

  public function subscribe(String $protocol, Array $channels, Callable $handler = null) {
    $msg = new Subscription(array(
      'command' => 'add',
      'protocol' => $protocol,
      'channels' => $channels
    ));
    return $this->execute($msg)->then(function($result) use ($protocol, $handler) {
      if (isset($result->failed_channels) && is_array($result->failed_channels)) {
        foreach($result->failed_channels as $channel) {
          $this->_removeSubscription($protocol, $channel);
        }
      }
      if (isset($result->subscribe_channels) && is_array($result->subscribe_channels)) {
        foreach($result->subscribe_channels as $channel) {
          $this->_addSubscription($protocol, $channel, $handler);
        }
      }

      return $result;
    });
  }

  private function _existsSubscription(String $protocol, String $channel) {
    return isset($this->_subscriptions[$protocol . $channel]);
  }

  private function _removeSubscription(String $protocol, String $channel) {
    if (!$this->_existsSubscription($protocol, $channel)) {
      return;
    }
    unset($this->_subscriptions[$protocol . $channel]);
    Handler::deRegister($protocol, null, $channel);
  }

  private function _addSubscription(String $protocol, String $channel, Callable $handler = null) {
    if ($this->_existsSubscription($protocol, $channel)) {
      return;
    }
    $this->_subscriptions[$protocol . $channel] = array('protocol' => $protocol, 'channel' => $channel);
    if (is_callable($handler)) {
      Handler::register($protocol, $handler, $channel);
    }
  }

  protected function getCalling() {
    if (!$this->_calling) {
      $this->_calling = new \SignalWire\Relay\Calling\Calling($this);
    }
    return $this->_calling;
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

  /**
   * Dynamic getter for the services we provide
   *
   * @param string $service to return
   * @return \Relay\Service The requested service object
   * @throws Exception For unknown context
   */
  public function __get($name) {
    $method = 'get' . ucfirst($name);
    if (method_exists($this, $method)) {
      return $this->$method();
    }
    $property = '_' . $name;
    if (property_exists($this, $property)) {
      return $this->$property;
    }
    throw new \Exception('Unknown service ' . $name);
  }
}
