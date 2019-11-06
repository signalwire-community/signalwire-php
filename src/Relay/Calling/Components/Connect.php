<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\ConnectState;
use SignalWire\Relay\Calling\Notification;
use SignalWire\Relay\Calling\Method;
use SignalWire\Relay\Calling\Event;

class Connect extends BaseComponent {
  public $eventType = Notification::Connect;
  public $method = Method::Connect;

  private $_devices;
  private $_ringback;

  public function __construct(Call $call, Array $devices, Array $ringback = []) {
    parent::__construct($call);

    $this->controlId = $call->tag;
    $this->_devices = $devices;
    $this->_ringback = $ringback;
  }

  public function payload() {
    $tmp = [
      'node_id' => $this->call->nodeId,
      'call_id' => $this->call->id,
      'devices' => $this->_devices
    ];
    if (is_array($this->_ringback) && count($this->_ringback) > 0) {
      $tmp['ringback'] = $this->_ringback;
    }
    return $tmp;
  }

  public function notificationHandler($params) {
    $this->state = $params->connect_state;

    $this->completed = $this->state !== ConnectState::Connecting;
    if ($this->completed) {
      $this->successful = $this->state === ConnectState::Connected;
      $this->event = new Event($params->connect_state, $params);
    }

    if ($this->_hasBlocker() && in_array($this->state, $this->_eventsToWait)) {
      ($this->blocker->resolve)();
    }
  }
}
