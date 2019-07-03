<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\CallState;
use SignalWire\Relay\Calling\Notification;

class Answer extends BaseComponent {
  public $eventType = Notification::State;

  public function __construct(Call $call) {
    parent::__construct($call);

    $this->controlId = $call->tag;
  }

  public function method() {
    return 'call.answer';
  }

  public function payload() {
    return [
      'node_id' => $this->call->nodeId,
      'call_id' => $this->call->id
    ];
  }

  public function notificationHandler($params) {
    if ($params->call_state === CallState::Answered) {
      $this->completed = true;
      $this->successful = true;
    }

    if ($this->_hasBlocker() && in_array($params->call_state, $this->_eventsToWait)) {
      ($this->blocker->resolve)();
    }
  }
}
