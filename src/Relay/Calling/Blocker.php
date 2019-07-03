<?php
namespace SignalWire\Relay\Calling;

use React\Promise\Promise;

class Blocker {
  public $controlId;
  public $eventType;
  public $promise;
  public $resolve;

  public function __construct(String $eventType, String $controlId) {
    $this->eventType = $eventType;
    $this->controlId = $controlId;

    $this->promise = new Promise(function (callable $resolve) {
      $this->resolve = $resolve;
    });
  }
}
