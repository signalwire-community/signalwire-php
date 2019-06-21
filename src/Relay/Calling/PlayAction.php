<?php
namespace SignalWire\Relay\Calling;

class PlayAction extends BaseAction {
  protected $baseMethod = 'call.play';

  public function update($params) {
    $this->state = $params->state;

    if ($this->state !== PlayState::Playing) {
      $this->finished = true;
      $this->result = new PlayResult($params);
    }
  }
}
