<?php

namespace SignalWire\Relay\Calling\Actions;

use SignalWire\Relay\Calling\Components\BaseComponent;

abstract class BaseAction {

  protected $component;

  public function __construct(BaseComponent $component) {
    $this->component = $component;
  }

  abstract function getResult();

  public function getControlId() {
    return $this->component->controlId;
  }

  public function getPayload() {
    return $this->component->payload;
  }

  public function isCompleted() {
    return $this->component->completed;
  }

  public function getState() {
    return $this->component->state;
  }
}
