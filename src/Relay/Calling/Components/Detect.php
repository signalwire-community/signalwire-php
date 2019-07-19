<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\DetectState;
use SignalWire\Relay\Calling\Notification;
use SignalWire\Relay\Calling\Event;

class Detect extends Controllable {
  public $eventType = Notification::Detect;

  public $type;
  public $result;

  protected $_eventsToWait = [DetectState::Finished, DetectState::Error];

  private $_events = [];
  private $_detect;
  private $_timeout;

  public function __construct(Call $call, Array $detect, Int $timeout = null) {
    parent::__construct($call);

    $this->_detect = $detect;
    $this->_timeout = $timeout;
  }

  public function method() {
    return 'call.detect';
  }

  public function payload() {
    if (!isset($this->_detect['params']) || is_null($this->_detect['params']) || (is_array($this->_detect['params']) && !count($this->_detect['params']))) {
      $this->_detect['params'] = new \stdClass;
    }
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
    $this->completed = in_array($this->state, $this->_eventsToWait);
    if ($this->completed) {
      $this->successful = $this->state !== DetectState::Error;
      $this->result = join('', $this->_events);
      $this->event = new Event($this->state, $detect);
    } else {
      array_push($this->_events, $detect->params->event);
    }

    if ($this->_hasBlocker() && in_array($this->state, $this->_eventsToWait)) {
      ($this->blocker->resolve)();
    }
  }
}
