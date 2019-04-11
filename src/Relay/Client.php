<?php
namespace SignalWire\Relay;
use SignalWire\Messages\Connect;

class Client {
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
   * WebSocket connection
   * @var SignalWire\Relay\Connection
   */
  protected $connection;

  public function __construct(Array $options) {
    $this->host = $options['host'];
    $this->project = $options['project'];
    $this->token = $options['token'];

    $this->connection = new Connection($this);
  }

  public function connect() {
    $this->connection->connect();
  }

  public function execute(\SignalWire\Messages\BaseMessage $msg) {
    return $this->connection->send($msg);
  }

  public function _onSocketOpen() {
    $bladeConnect = new Connect($this->project, $this->token); // $this->sessionId
    $this->execute($bladeConnect)->then(
      function($result) {
        \SignalWire\Log::warning('Connect result:', [$result]);
      }, function($error) {
        \SignalWire\Log::warning('Connect error:', [$error]);
      }
    );
  }

  public function _onSocketClose() {

  }

  public function _onSocketError($error) {
    \SignalWire\Log::error('Socket Error:', [$error->getMessage()]);
  }

  public function _onSocketMessage($msg) {

  }
}
