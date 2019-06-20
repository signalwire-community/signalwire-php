<?php
namespace SignalWire\Relay\Calling;

class PromptAction extends BaseAction {
  protected $baseMethod = 'call.play_and_collect';

  public function update($params) {
    switch ($params->result->type) {
      case 'digit':
      case 'speech':
        $this->state = 'successful';
        break;
      default:
        $this->state = $params->result->type;
    }
    $this->finished = true;
    // TODO: Use "new PromptResult()" here
    $this->result = $params;
  }
}
