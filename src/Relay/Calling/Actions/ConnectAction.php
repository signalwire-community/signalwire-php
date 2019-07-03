<?php

namespace SignalWire\Relay\Calling\Actions;

use SignalWire\Relay\Calling\Results\ConnectResult;

class ConnectAction extends BaseAction {

  public function getResult() {
    return new ConnectResult($this->component);
  }

}
