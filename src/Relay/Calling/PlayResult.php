<?php
namespace SignalWire\Relay\Calling;

class PlayResult {
  public $controlId;
  public $state;
  public $succeeded;
  public $failed;

  public function __construct($params) {
    $this->controlId = $params->control_id;
    $this->state = $params->state;
    $this->succeeded = $this->state === PlayState::Finished;
    $this->failed = $this->state === PlayState::Error;
  }
}
