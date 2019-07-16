<?php

namespace SignalWire\Relay\Calling\Results;

use SignalWire\Relay\Calling\Components\Detect;

class DetectResult extends BaseResult {

  public function __construct(Detect $component) {
    parent::__construct($component);
  }

  public function getType() {
    return $this->component->type;
  }

  public function getResult() {
    return $this->component->result;
  }

}
