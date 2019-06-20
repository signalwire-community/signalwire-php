<?php
namespace SignalWire\Relay\Calling;
use SignalWire\Messages\Execute;
use Ramsey\Uuid\Uuid;

abstract class BaseAction {
  protected $baseMethod = '';

  public $call;
  public $controlId;
  public $finished = false;
  public $state = null;
  public $result = null;

  abstract function update($params);

  public function __construct(Call $call) {
    $this->call = $call;
    $this->controlId = Uuid::uuid4()->toString();
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
