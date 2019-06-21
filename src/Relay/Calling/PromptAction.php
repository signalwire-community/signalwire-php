<?php
namespace SignalWire\Relay\Calling;

class PromptAction extends BaseAction {
  protected $baseMethod = 'call.play_and_collect';

  public function update($params) {
    switch ($params->result->type) {
      case PromptState::Digit:
      case PromptState::Speech:
        $this->state = 'successful';
        break;
      default:
        $this->state = $params->result->type;
    }
    $this->finished = true;
    $this->result = new PromptResult($params);
  }
}
