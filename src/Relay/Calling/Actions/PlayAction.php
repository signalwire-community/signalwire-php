<?php

namespace SignalWire\Relay\Calling\Actions;

use SignalWire\Relay\Calling\Results\PlayResult;

class PlayAction extends BaseAction {

  public function getResult() {
    return new PlayResult($this->component);
  }

  public function stop() {
    return $this->component->stop();
  }

}
