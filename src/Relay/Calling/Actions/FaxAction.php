<?php

namespace SignalWire\Relay\Calling\Actions;

use SignalWire\Relay\Calling\Results\FaxResult;

class FaxAction extends BaseAction {

  public function getResult() {
    return new FaxResult($this->component);
  }

  public function stop() {
    return $this->component->stop();
  }

}
