<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Messages\Execute;
use SignalWire\Relay\Calling\Results\StopResult;

abstract class Controllable extends BaseComponent {

  public function stop() {
    return $this->_execute("{$this->method()}.stop")->then(function ($result) {
      if ($result->code !== '200') {
        $this->terminate();
      }
      return new StopResult($result->code === '200');
    });
  }

  public function pause($resultKlass) {
    return $this->_execute("{$this->method()}.pause")->then(function($result) use ($resultKlass) {
      return new $resultKlass($result->code === '200');
    });
  }

  public function resume($resultKlass) {
    return $this->_execute("{$this->method()}.resume")->then(function($result) use ($resultKlass) {
      return new $resultKlass($result->code === '200');
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
      return (object)[
        'code' => $error->getCode(),
        'message' => $error->getMessage()
      ];
    });
  }
}
