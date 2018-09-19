<?php
namespace SignalWire\Rest;

class Client extends \Twilio\Rest\Client {
  public function __construct(...$args) {
    parent::__construct(...$args);

    $this->_api = new Api($this);
  }
}