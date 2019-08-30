<?php

namespace SignalWire\Relay\Calling\Results;

use SignalWire\Relay\Calling\Components\BaseComponent;

abstract class BaseResult {

  public $component;

  public function __construct(BaseComponent $component) {
    $this->component = $component;
  }

  public function isSuccessful() {
    return $this->component->successful;
  }

  public function getEvent() {
    return $this->component->event;
  }
}
