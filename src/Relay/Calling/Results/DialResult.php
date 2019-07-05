<?php

namespace SignalWire\Relay\Calling\Results;

use SignalWire\Relay\Calling\Components\Dial;

class DialResult extends BaseResult {

  public function __construct(Dial $component) {
    parent::__construct($component);
  }

  public function getCall() {
    return $this->component->call;
  }
}
