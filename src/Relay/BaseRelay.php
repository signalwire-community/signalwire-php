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

  public function registerContexts($contexts, Callable $handler) {
    return Setup::receive($this->client, $contexts)->then(function($success) use ($contexts, $handler) {
      if ($success) {
        foreach ((array) $contexts as $context) {
          Handler::register($this->client->relayProtocol, $handler, $this->_prefixCtx($context));
        }
      }
    });
  }

  protected function _prefixCtx(String $context) {
    return "{$this->service}.context.{$context}";
  }
}
