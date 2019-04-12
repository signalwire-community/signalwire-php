<?php
namespace SignalWire\Relay;
use SignalWire\Log;

abstract class BaseRelay {
  protected $protocol;
  protected $ready;

  abstract function getServiceName(): String;

  public function __construct(Client $client) {
    $this->ready = new \React\Promise\Promise(function (callable $resolve, callable $reject) {
      echo PHP_EOL . "Setup " . $this->getServiceName();
      $this->protocol = '';
      call_user_func($resolve, $this->protocol);
    });
  }

  // protected function toJson(Bool $pretty = false){
  //   return $pretty ? json_encode($this->request, JSON_PRETTY_PRINT) : json_encode($this->request);
  // }
}
