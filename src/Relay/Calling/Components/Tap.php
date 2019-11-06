<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\TapState;
use SignalWire\Relay\Calling\Notification;
use SignalWire\Relay\Calling\Method;
use SignalWire\Relay\Calling\Event;

class Tap extends Controllable {
  public $eventType = Notification::Tap;
  public $method = Method::Tap;
  public $tap;
  public $device;

  private $_tap;
  private $_device;

  public function __construct(Call $call, Array $tap, Array $device) {
    parent::__construct($call);

    $this->_tap = $tap;
    $this->_device = $device;
  }

  public function payload() {
    $this->_tap['params'] = (object) $this->_tap['params'];
    $this->_device['params'] = (object) $this->_device['params'];
    return [
      'node_id' => $this->call->nodeId,
      'call_id' => $this->call->id,
      'control_id' => $this->controlId,
      'tap' => $this->_tap,
      'device' => $this->_device
    ];
  }

  public function getSourceDevice() {
    if ($this->_executeResult && isset($this->_executeResult->source_device)) {
      return $this->_executeResult->source_device;
    }
    return null;
  }

  public function notificationHandler($params) {
    $this->tap = $params->tap;
    $this->device = $params->device;
    $this->state = $params->state;

    $this->completed = $this->state === TapState::Finished;
    if ($this->completed) {
      $this->successful = true;
      $this->event = new Event($params->state, $params);
    }

    if ($this->_hasBlocker() && in_array($this->state, $this->_eventsToWait)) {
      ($this->blocker->resolve)();
    }
  }
}
