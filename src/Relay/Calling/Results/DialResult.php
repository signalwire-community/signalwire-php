<?php

namespace SignalWire\Relay\Calling\Results;

use SignalWire\Relay\Calling\Components\Dial;

class DialResult extends BaseResult {

  public function __construct(Dial $component) {
    parent::__construct($component);
  }

}
