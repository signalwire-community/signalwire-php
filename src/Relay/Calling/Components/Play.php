<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\PlayState;
use SignalWire\Relay\Calling\Notification;
use SignalWire\Relay\Calling\Method;
use SignalWire\Relay\Calling\Event;

class Play extends Controllable {
  public $eventType = Notification::Play;
  public $method = Method::Play;

  private $_play;
  private $_volume;

  public function __construct(Call $call, $play, $volume = 0) {
    parent::__construct($call);

    $this->_play = $play;
    $this->_volume = (float)$volume;
  }

  public function payload() {
    $tmp = [
      'node_id' => $this->call->nodeId,
      'call_id' => $this->call->id,
      'control_id' => $this->controlId,
      'play' => $this->_play
    ];
    if ($this->_volume !== 0.0) {
      $tmp['volume'] = $this->_volume;
    }
    return $tmp;
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
