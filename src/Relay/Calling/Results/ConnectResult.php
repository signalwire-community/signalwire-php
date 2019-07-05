<?php

namespace SignalWire\Relay\Calling\Results;

use SignalWire\Relay\Calling\Components\Connect;

class ConnectResult extends BaseResult {

  public function __construct(Connect $component) {
    parent::__construct($component);
  }

  public function getCall() {
    return $this->component->call->peer;
  }
}
