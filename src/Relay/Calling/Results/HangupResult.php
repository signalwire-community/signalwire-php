<?php

namespace SignalWire\Relay\Calling\Results;

use SignalWire\Relay\Calling\Components\Hangup;

class HangupResult extends BaseResult {

  public function __construct(Hangup $component) {
    parent::__construct($component);
  }

  public function getReason() {
    return $this->component->reason;
  }

}
