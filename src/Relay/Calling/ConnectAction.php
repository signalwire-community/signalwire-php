<?php
namespace SignalWire\Relay\Calling;

class ConnectAction extends BaseAction {
  // FIXME: call.connect can not be stopped!
  protected $baseMethod = 'call.connect';

  public function update($params) {
    // TODO: add Result/Call and Payload
    $this->state = $params->connect_state;
    $this->finished = $this->state !== ConnectState::Connecting;
  }
}
