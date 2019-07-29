<?php
namespace SignalWire\Relay;

use SignalWire\Handler;

abstract class BaseRelay {

  public $client;
  protected $service;

  abstract function notificationHandler($notification): void;

  public function __construct(Client $client) {
    $this->client = $client;
    $this->service = strtolower((new \ReflectionClass($this))->getShortName());
  }

  public function onReceive(Array $contexts, Callable $handler) {
    return Setup::receive($this->client, $contexts)->then(function($success) use ($contexts, $handler) {
      if ($success) {
        foreach ($contexts as $context) {
          Handler::register($this->client->relayProtocol, $handler, $this->_ctxReceiveUniqueId($context));
        }
      }
    });
  }

  public function onStateChange(Array $contexts, Callable $handler) {
    return Setup::receive($this->client, $contexts)->then(function($success) use ($contexts, $handler) {
      if ($success) {
        foreach ($contexts as $context) {
          Handler::register($this->client->relayProtocol, $handler, $this->_ctxStateUniqueId($context));
        }
      }
    });
  }

  protected function _ctxReceiveUniqueId(String $context) {
    return "{$this->service}.ctxReceive.{$context}";
  }

  protected function _ctxStateUniqueId(String $context) {
    return "{$this->service}.ctxState.{$context}";
  }
}
