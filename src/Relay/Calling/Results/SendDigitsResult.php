<?php

namespace SignalWire\Relay\Calling\Results;

use SignalWire\Relay\Calling\Components\SendDigits;

class SendDigitsResult extends BaseResult {

  public function __construct(SendDigits $component) {
    parent::__construct($component);
  }
}
