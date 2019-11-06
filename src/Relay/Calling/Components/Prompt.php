<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\PromptState;
use SignalWire\Relay\Calling\Notification;
use SignalWire\Relay\Calling\Method;
use SignalWire\Relay\Calling\Event;

class Prompt extends Controllable {
  public $eventType = Notification::Collect;
  public $method = Method::PlayAndCollect;

  public $type;
  public $input;
  public $terminator;
  public $confidence;

  private $_collect;
  private $_play;
  private $_volume;

  public function __construct(Call $call, $collect, $play, $volume = 0) {
    parent::__construct($call);

    $this->_collect = $collect;
    $this->_play = $play;
    $this->_volume = (float)$volume;
  }

  public function payload() {
    $tmp = [
      'node_id' => $this->call->nodeId,
      'call_id' => $this->call->id,
      'control_id' => $this->controlId,
      'play' => $this->_play,
      'collect' => $this->_collect
    ];
    if ($this->_volume !== 0.0) {
      $tmp['volume'] = $this->_volume;
    }
    return $tmp;
  }

  public function notificationHandler($params) {
    $this->completed = true;

    $this->type = $params->result->type;
    $this->event = new Event($this->type, $params->result);
    switch ($this->type) {
      case PromptState::Digit:
        $this->state = 'successful';
        $this->successful = true;
        $this->input = $params->result->params->digits;
        $this->terminator = $params->result->params->terminator;
        break;
      case PromptState::Speech:
        $this->state = 'successful';
        $this->successful = true;
        $this->input = $params->result->params->text;
        $this->confidence = $params->result->params->confidence;
        break;
      default:
        $this->state = $this->type;
        $this->successful = false;
    }

    if ($this->_hasBlocker() && in_array($this->type, $this->_eventsToWait)) {
      ($this->blocker->resolve)();
    }
  }
}
