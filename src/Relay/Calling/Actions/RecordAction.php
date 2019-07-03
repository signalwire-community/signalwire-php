<?php

namespace SignalWire\Relay\Calling\Actions;

use SignalWire\Relay\Calling\Results\RecordResult;

class RecordAction extends BaseAction {

  public function getResult() {
    return new RecordResult($this->component);
  }

  public function stop() {
    return $this->component->stop();
  }

}
