<?php

namespace SignalWire\Relay\Calling\Components;

class FaxReceive extends BaseFax {

  public function method() {
    return 'calling.receive_fax';
  }

  public function payload() {
    return [
      'node_id' => $this->call->nodeId,
      'call_id' => $this->call->id,
      'control_id' => $this->controlId
    ];
  }
}
