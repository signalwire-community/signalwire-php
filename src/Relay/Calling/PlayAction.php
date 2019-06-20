<?php
namespace SignalWire\Relay\Calling;

class PlayAction extends BaseAction {
  protected $baseMethod = 'call.play';

  public function update($params) {
    $this->state = $params->state;
    $finished = $this->state == 'finished' || $this->state == 'error';
    if ($finished) {
      $this->finished = true;
      // TODO: Use "new PlayResult()" here
      $this->result = $params;
    }
  }
}
