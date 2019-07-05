<?php
namespace SignalWire\Relay;
use SignalWire\Messages\Execute;
use SignalWire\Util\Events;
use SignalWire\Handler;

abstract class BaseRelay {
  const SetupProtocol = 'signalwire';
  const SetupMethod = 'setup';
  const SetupChannels = array('notifications');

  public $ready;
  public $protocol;
  public $client;

  abstract function getServiceName(): String;
  abstract function notificationHandler($notification): void;

  public function __construct(Client $client) {
    $this->client = $client;
    $this->ready = new \React\Promise\Promise(function (callable $resolve, callable $reject) use ($client) {
      $msg = new Execute(array(
        'protocol' => self::SetupProtocol,
        'method' => self::SetupMethod,
        'params' => array('service' => $this->getServiceName())
      ));
      $client->execute($msg)->done(function($response) use ($client, $resolve, $reject) {
        $client->subscribe($response->result->protocol, self::SetupChannels, [$this, "notificationHandler"])->done(function($response) use ($resolve) {
          $this->protocol = $response->protocol;
          call_user_func($resolve, $this->protocol);
          Handler::registerOnce(Events::SocketClose, [$this, "_cleanup"], $this->client->uuid);
        }, $reject);
      }, $reject);
    });
  }

  public function _cleanup() {
    if ($this->protocol) {
      Handler::deRegisterAll($this->protocol);
    }
  }
}
