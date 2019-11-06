<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\DetectState;
use SignalWire\Relay\Calling\DetectType;
use SignalWire\Relay\Calling\Notification;
use SignalWire\Relay\Calling\Method;
use SignalWire\Relay\Calling\Event;

class Detect extends Controllable {
  public $eventType = Notification::Detect;
  public $method = Method::Detect;

  public $type;
  public $result;

  protected $_eventsToWait = [DetectState::Finished, DetectState::Error];

  private $_events = [];
  private $_detect;
  private $_timeout;
  private $_waitForBeep;
  private $_waitingForReady = false;

  public function __construct(Call $call, Array $detect, Int $timeout = null, bool $waitForBeep = false) {
    parent::__construct($call);

    $this->_detect = $detect;
    $this->_timeout = $timeout;
    $this->_waitForBeep = $waitForBeep;
  }

  public function payload() {
    $this->_detect['params'] = (object) $this->_detect['params'];
    $payload = [
      'node_id' => $this->call->nodeId,
      'call_id' => $this->call->id,
      'control_id' => $this->controlId,
      'detect' => $this->_detect
    ];
    if ($this->_timeout) {
      $payload['timeout'] = $this->_timeout;
    }
    return $payload;
  }

  public function notificationHandler($params) {
    $detect = $params->detect;
    $this->type = $detect->type;
    $this->state = $detect->params->event;

    $finishedEvents = [DetectState::Finished, DetectState::Error];
    if (in_array($this->state, $finishedEvents)) {
      return $this->_complete($detect);
    }

    if (!$this->_hasBlocker()) {
      array_push($this->_events, $detect->params->event);
      return;
    }

    if ($this->type === DetectType::Digit) {
      return $this->_complete($detect);
    }

    if ($this->_waitingForReady) {
      if ($this->state === DetectState::Ready) {
        return $this->_complete($detect);
      }
      return;
    }

    if ($this->_waitForBeep && $this->state === DetectState::Machine) {
      $this->_waitingForReady = true;
      return;
    }

    if (in_array($this->state, $this->_eventsToWait)) {
      return $this->_complete($detect);
    }
  }

  private function _complete($detect) {
    $this->completed = true;
    $this->event = new Event($this->state, $detect);

    if ($this->_hasBlocker()) {
      // force READY/NOT_READY to MACHINE
      if (in_array($this->state, [DetectState::Ready, DetectState::NotReady])) {
        $this->result = DetectState::Machine;
      } elseif (!in_array($this->state, [DetectState::Finished, DetectState::Error])) {
        $this->result = $this->state;
      }
      $this->successful = !in_array($this->state, [DetectState::Finished, DetectState::Error]);
      ($this->blocker->resolve)();
    } else {
      $this->result = join(',', $this->_events);
      $this->successful = $this->state !== DetectState::Error;
    }
  }
}
