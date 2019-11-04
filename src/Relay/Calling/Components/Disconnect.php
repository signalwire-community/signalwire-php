<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\ConnectState;
use SignalWire\Relay\Calling\Notification;
use SignalWire\Relay\Calling\Event;

class Disconnect extends BaseComponent {
  public $eventType = Notification::Connect;

  public function __construct(Call $call) {
    parent::__construct($call);

    $this->controlId = $call->tag;
  }

  public function method() {
    return 'calling.disconnect';
  }

  public function payload() {
    return [
      'node_id' => $this->call->nodeId,
      'call_id' => $this->call->id
    ];
  }

  public function notificationHandler($params) {
    // FIXME: Check the calling.call.connect events!!

    $this->state = $params->connect_state;

    $this->completed = $this->state !== ConnectState::Connecting;
    if ($this->completed) {
      $this->successful = $this->state === ConnectState::Disconnected;
      $this->event = new Event($params->connect_state, $params);
    }

    if ($this->_hasBlocker() && in_array($this->state, $this->_eventsToWait)) {
      ($this->blocker->resolve)();
    }
  }
}
