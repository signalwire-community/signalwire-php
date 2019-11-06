<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\CallState;
use SignalWire\Relay\Calling\Notification;
use SignalWire\Relay\Calling\Method;
use SignalWire\Relay\Calling\Event;

class Dial extends BaseComponent {
  public $eventType = Notification::State;
  public $method = Method::Begin;

  public function __construct(Call $call) {
    parent::__construct($call);

    $this->controlId = $call->tag;
  }

  public function payload() {
    return [
      'tag' => $this->call->tag,
      'device' => $this->call->getDevice()
    ];
  }

  public function notificationHandler($params) {
    $this->state = $params->call_state;

    $this->completed = in_array($this->state, [CallState::Answered, CallState::Ending, CallState::Ended]);
    if ($this->completed) {
      $this->successful = $this->state === CallState::Answered;
      $this->event = new Event($params->call_state, $params);
    }

    if ($this->_hasBlocker() && in_array($this->state, $this->_eventsToWait)) {
      ($this->blocker->resolve)();
    }
  }
}
