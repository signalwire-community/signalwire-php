<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\PlayState;
use SignalWire\Relay\Calling\Notification;
use SignalWire\Relay\Calling\Event;

class Play extends Controllable {
  public $eventType = Notification::Play;

  private $_play;

  public function __construct(Call $call, $play) {
    parent::__construct($call);

    $this->_play = $play;
  }

  public function method() {
    return 'calling.play';
  }

  public function payload() {
    return [
      'node_id' => $this->call->nodeId,
      'call_id' => $this->call->id,
      'control_id' => $this->controlId,
      'play' => $this->_play
    ];
  }

  public function notificationHandler($params) {
    $this->state = $params->state;

    $this->completed = $this->state !== PlayState::Playing;
    if ($this->completed) {
      $this->successful = $this->state === PlayState::Finished;
      $this->event = new Event($params->state, $params);
    }

    if ($this->_hasBlocker() && in_array($this->state, $this->_eventsToWait)) {
      ($this->blocker->resolve)();
    }
  }
}
