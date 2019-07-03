<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\PromptState;
use SignalWire\Relay\Calling\Notification;

class Prompt extends Controllable {
  public $eventType = Notification::Collect;

  public $type;
  public $input;
  public $terminator;
  public $confidence;

  private $_collect;
  private $_play;

  public function __construct(Call $call, $collect, $play) {
    parent::__construct($call);

    $this->_collect = $collect;
    $this->_play = $play;
  }

  public function method() {
    return 'call.play_and_collect';
  }

  public function payload() {
    return [
      'node_id' => $this->call->nodeId,
      'call_id' => $this->call->id,
      'control_id' => $this->controlId,
      'play' => $this->_play,
      'collect' => $this->_collect
    ];
  }

  public function notificationHandler($params) {
    $this->completed = true;

    $this->event = $params->result;
    $this->type = $params->result->type;
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
