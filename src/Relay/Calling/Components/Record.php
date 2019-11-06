<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\RecordState;
use SignalWire\Relay\Calling\Notification;
use SignalWire\Relay\Calling\Method;
use SignalWire\Relay\Calling\Event;

class Record extends Controllable {
  public $eventType = Notification::Record;
  public $method = Method::Record;

  public $url;
  public $duration;
  public $size;

  private $_record;

  public function __construct(Call $call, $record) {
    parent::__construct($call);

    $this->_record = $record;
  }

  public function payload() {
    return [
      'node_id' => $this->call->nodeId,
      'call_id' => $this->call->id,
      'control_id' => $this->controlId,
      'record' => $this->_record
    ];
  }

  public function notificationHandler($params) {
    $this->state = $params->state;

    $this->completed = $this->state !== RecordState::Recording;
    if ($this->completed) {
      $this->successful = $this->state === RecordState::Finished;
      $this->event = new Event($params->state, $params);
      $this->url = $params->url;
      $this->duration = $params->duration;
      $this->size = $params->size;
    }

    if ($this->_hasBlocker() && in_array($this->state, $this->_eventsToWait)) {
      ($this->blocker->resolve)();
    }
  }
}
