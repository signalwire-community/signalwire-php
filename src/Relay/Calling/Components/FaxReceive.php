<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Method;

class FaxReceive extends BaseFax {
  public $method = Method::ReceiveFax;

  public function payload() {
    return [
      'node_id' => $this->call->nodeId,
      'call_id' => $this->call->id,
      'control_id' => $this->controlId
    ];
  }
}
