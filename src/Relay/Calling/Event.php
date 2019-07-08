<?php
namespace SignalWire\Relay\Calling;

class Event {
  public $name;
  public $payload;

  public function __construct(String $name, $payload) {
    $this->name = $name;
    $this->payload = $payload;
  }
}
