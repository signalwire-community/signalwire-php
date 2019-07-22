<?php

namespace SignalWire\Relay\Calling\Results;

use SignalWire\Relay\Calling\Components\Tap;

class TapResult extends BaseResult {

  public function __construct(Tap $component) {
    parent::__construct($component);
  }

  public function getTap() {
    return $this->component->tap;
  }

  public function getSourceDevice() {
    return $this->component->getSourceDevice();
  }

  public function getDestinationDevice() {
    return $this->component->device;
  }

}
