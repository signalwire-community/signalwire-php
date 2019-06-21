<?php
namespace SignalWire\Relay\Calling;

class RecordAction extends BaseAction {
  protected $baseMethod = 'call.record';

  public function update($params) {
    $this->state = $params->state;

    if ($this->state !== RecordState::Recording) {
      $this->finished = true;
      $this->result = new RecordResult($params);
    }
  }
}
