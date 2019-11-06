<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\SendDigitsState;
use SignalWire\Relay\Calling\Notification;
use SignalWire\Relay\Calling\Method;
use SignalWire\Relay\Calling\Event;

class SendDigits extends BaseComponent {
  public $eventType = Notification::SendDigits;
  public $method = Method::SendDigits;
  public $digits;

  public function __construct(Call $call, String $digits) {
    parent::__construct($call);

    $this->digits = $digits;
  }

  public function payload() {
    return [
      'node_id' => $this->call->nodeId,
      'call_id' => $this->call->id,
      'control_id' => $this->controlId,
      'digits' => $this->digits
    ];
  }

  public function notificationHandler($params) {
    $this->state = $params->state;

    $this->completed = $this->state === SendDigitsState::Finished;
    $this->successful = $this->completed;
    $this->event = new Event($params->state, $params);

    if ($this->_hasBlocker() && in_array($this->state, $this->_eventsToWait)) {
      ($this->blocker->resolve)();
    }
  }
}
