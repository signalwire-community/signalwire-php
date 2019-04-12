<?php
namespace SignalWire\Relay;
use SignalWire\Messages\BaseMessage;
use SignalWire\Messages\Connect;
use SignalWire\Util\Events;
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

  public function __construct(Array $options) {
    $this->host = $options['host'];
    $this->project = $options['project'];
    $this->token = $options['token'];

    $this->uuid = \SignalWire\Util\UUID::v4();
    $this->connection = new Connection($this);
    $this->_attachListeners();
    Handler::view();
  }

  public function connect() {
    $this->connection->connect();
  }

  public function execute(BaseMessage $msg) {
    return $this->connection->send($msg);
  }

  public function _onSocketOpen() {
    $bladeConnect = new Connect($this->project, $this->token, $this->sessionid);
    $this->execute($bladeConnect)->then(
      function($result) {
        $this->_autoReconnect = true;
        $this->sessionid = $result->sessionid;
        $this->nodeid = $result->nodeid;
        // if ($result->session_restored) { TODO: }

        Handler::trigger(Events::Ready, $this, $this->uuid);
      }, function($error) {
        Handler::trigger(Events::Error, $error, $this->uuid);
      }
    );
  }

  public function _onSocketClose() {
    if ($this->_autoReconnect === false) {
      return;
    }
    $self = $this;
    $loop = React\EventLoop\Factory::create();
    $loop->addTimer(1.0, function() use ($loop, $self) {
      $self->connect();
      $loop->stop();
    });
  }

  public function _onSocketError($error) {
    Handler::trigger(Events::Error, $error, $this->uuid);
  }

  public function _onSocketMessage($msg) {

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
    $this->off(Events::SocketOpen, [$this, "_onSocketOpen"], $this->uuid );
    $this->off(Events::SocketClose, [$this, "_onSocketClose"], $this->uuid);
    $this->off(Events::SocketError, [$this, "_onSocketError"], $this->uuid);
    $this->off(Events::SocketMessage, [$this, "_onSocketMessage"], $this->uuid);
  }
}
