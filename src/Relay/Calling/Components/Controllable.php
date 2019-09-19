<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Messages\Execute;
use SignalWire\Relay\Calling\Results\BaseResult;
// use SignalWire\Relay\Calling\Event;

abstract class Controllable extends BaseComponent {

  public function stop() {
    return $this->_execute("{$this->method()}.stop");
  }

  public function pause(BaseResult $resultKlass) {
    return $this->_execute("{$this->method()}.pause")->then(function() use ($resultKlass) {
      // $this->event = new Event('', []); // TODO: use the execute response?
      return new $resultKlass($this);
    });
  }

  public function resume(BaseResult $resultKlass) {
    return $this->_execute("{$this->method()}.resume")->then(function() use ($resultKlass) {
      // $this->event = new Event('', []); // TODO: use the execute response?
      return new $resultKlass($this);
    });
  }

  private function _execute(string $method) {
    $msg = new Execute([
      'protocol' => $this->call->relayInstance->client->relayProtocol,
      'method' => $method,
      'params' => [
        'node_id' => $this->call->nodeId,
        'call_id' => $this->call->id,
        'control_id' => $this->controlId
      ]
    ]);

    return $this->call->_execute($msg)->otherwise(function($error) {
      $this->terminate();
      return (object)[
        'code' => $error->getCode(),
        'message' => $error->getMessage()
      ];
    });
  }
}
