<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\CallState;
use SignalWire\Relay\Calling\Notification;
use SignalWire\Relay\Calling\Method;
use SignalWire\Relay\Calling\Event;

class Hangup extends BaseComponent {
  public $eventType = Notification::State;
  public $method = Method::End;
  public $reason;

  public function __construct(Call $call, String $reason) {
    parent::__construct($call);

    $this->controlId = $call->tag;
    $this->reason = $reason;
  }

  public function payload() {
    return [
      'node_id' => $this->call->nodeId,
      'call_id' => $this->call->id,
      'reason' => $this->reason
    ];
  }

  public function notificationHandler($params) {
    $this->state = $params->call_state;

    $this->completed = $this->state === CallState::Ended;
    if ($this->completed) {
      $this->successful = true;
      $this->event = new Event($params->call_state, $params);

      if (isset($params->end_reason)) {
        $this->reason = $params->end_reason;
      }
    }

    if ($this->_hasBlocker() && in_array($this->state, $this->_eventsToWait)) {
      ($this->blocker->resolve)();
    }
  }
}
