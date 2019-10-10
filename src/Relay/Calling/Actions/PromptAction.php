<?php

namespace SignalWire\Relay\Calling\Actions;

use SignalWire\Relay\Calling\Results\PromptResult;
use SignalWire\Relay\Calling\Results\PromptVolumeResult;

class PromptAction extends BaseAction {

  public function getResult() {
    return new PromptResult($this->component);
  }

  public function stop() {
    return $this->component->stop();
  }

  public function volume($value) {
    return $this->component->volume($value)->then(function($result) {
      return new PromptVolumeResult($result);
    });
  }
}
