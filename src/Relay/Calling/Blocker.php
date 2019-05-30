<?php
namespace SignalWire\Relay\Calling;
use React\Promise\Promise;

class Blocker {
  public $controlId;
  public $eventType;
  public $resolver;
  public $promise;
  public $resolve;
  public $reject;

  public function __construct(String $controlId, String $eventType, Callable $resolver) {
    $this->controlId = $controlId;
    $this->eventType = $eventType;
    $this->resolver = $resolver;

    $this->promise = new Promise(function (callable $resolve, callable $reject) {
      $this->resolve = $resolve;
      $this->reject = $reject;
    });
  }
}
