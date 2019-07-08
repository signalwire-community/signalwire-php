<?php
namespace SignalWire\Relay;

abstract class BaseRelay {

  public $client;

  abstract function notificationHandler($notification): void;

  public function __construct(Client $client) {
    $this->client = $client;
  }

}
