<?php

namespace SignalWire\Relay\Calling\Actions;

use SignalWire\Relay\Calling\Results\PromptResult;

class PromptAction extends BaseAction {

  public function getResult() {
    return new PromptResult($this->component);
  }

  public function stop() {
    return $this->component->stop();
  }

}
