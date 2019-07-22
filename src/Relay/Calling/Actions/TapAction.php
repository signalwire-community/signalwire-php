<?php

namespace SignalWire\Relay\Calling\Actions;

use SignalWire\Relay\Calling\Results\TapResult;

class TapAction extends BaseAction {

  public function getResult() {
    return new TapResult($this->component);
  }

  public function getSourceDevice() {
    return $this->component->getSourceDevice();
  }

  public function stop() {
    return $this->component->stop();
  }

}
