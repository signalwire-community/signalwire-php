<?php

namespace SignalWire\Relay\Calling\Results;

use SignalWire\Relay\Calling\Components\Disconnect;

class DisconnectResult extends BaseResult {

  public function __construct(Disconnect $component) {
    parent::__construct($component);
  }

}
