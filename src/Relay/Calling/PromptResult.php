<?php
namespace SignalWire\Relay\Calling;

class PromptResult {
  public $controlId;
  public $state;
  public $type;
  public $result;
  public $succeeded;
  public $failed;

  public function __construct($params) {
    $this->controlId = $params->control_id;

    switch ($params->result->type) {
      case PromptState::Digit:
      case PromptState::Speech:
        $this->state = 'successful';
        $this->type = $params->result->type;
        $this->result = $params->result->params;
        $this->succeeded = true;
        $this->failed = false;
        break;
      default:
        $this->state = $params->result->type;
        $this->succeeded = false;
        $this->failed = true;
    }
  }
}
