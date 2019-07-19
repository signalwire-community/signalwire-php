<?php

namespace SignalWire\Relay\Calling\Actions;

use SignalWire\Relay\Calling\Results\DetectResult;

class DetectAction extends BaseAction {

  public function getResult() {
    return new DetectResult($this->component);
  }

  public function stop() {
    return $this->component->stop();
  }

}
