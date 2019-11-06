<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\Notification;
use SignalWire\Relay\Calling\Event;

class Await extends BaseComponent {
  public $eventType = Notification::State;
  public $event;

  public function __construct(Call $call) {
    parent::__construct($call);

    $this->controlId = $call->tag;
  }

  public function payload() {
    return null;
  }

  public function notificationHandler($params) {
    if ($this->_hasBlocker() && in_array($params->call_state, $this->_eventsToWait)) {
      $this->completed = true;
      $this->successful = true;
      $this->event = new Event($params->call_state, $params);
      ($this->blocker->resolve)();
    }
  }
}
