<?php
namespace SignalWire\Relay\Calling;
use SignalWire\Messages\Execute;

abstract class BaseAction {
  public $call;
  protected $controlId;
  protected $baseMethod = '';

  public function __construct(Call $call, String $controlId) {
    $this->call = $call;
    $this->controlId = $controlId;
  }

  public function stop() {
    $msg = new Execute([
      'protocol' => $this->call->relayInstance->protocol,
      'method' => "{$this->baseMethod}.stop",
      'params' => [
        'node_id' => $this->call->nodeId,
        'call_id' => $this->call->id,
        'control_id' => $this->controlId
      ]
    ]);

    return $this->call->_execute($msg);
  }
}
