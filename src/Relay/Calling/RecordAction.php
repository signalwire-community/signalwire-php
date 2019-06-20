<?php
namespace SignalWire\Relay\Calling;

class RecordAction extends BaseAction {
  protected $baseMethod = 'call.record';

  public function update($params) {
    $this->state = $params->state;
    $finished = $this->state == 'finished' || $this->state == 'no_input';
    if ($finished) {
      $this->finished = true;
      // TODO: Use "new RecordResult()" here
      $this->result = $params;
    }
  }
}
