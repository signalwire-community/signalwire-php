<?php
namespace SignalWire\Relay;

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

  public function _onWsOpen() {
    echo "OPEN" . PHP_EOL;
    $bladeConnect = '{"jsonrpc":"2.0","id":"f037ccd0-8e62-4269-a944-ae3ea1c716fe","method":"blade.connect","params":{"version":{"major":2,"minor":1,"revision":0},"authentication":{"project":"'.$this->project.'","token":"'.$this->token.'"}}}';
    $this->connection->send($bladeConnect);
  }

  public function _onWsError($error) {
    echo "Not Connected with error";
    // print_r($error->message);
    echo $error->getMessage();
  }
}
