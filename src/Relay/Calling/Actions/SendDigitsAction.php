<?php

namespace SignalWire\Relay\Calling\Actions;

use SignalWire\Relay\Calling\Results\SendDigitsResult;

class SendDigitsAction extends BaseAction {

  public function getResult() {
    return new SendDigitsResult($this->component);
  }

}
