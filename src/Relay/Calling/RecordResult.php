<?php
namespace SignalWire\Relay\Calling;

class RecordResult {
  public $controlId;
  public $state;
  public $url;
  public $result;
  public $succeeded;
  public $failed;

  public function __construct($params) {
    $this->controlId = $params->control_id;
    $this->state = $params->state;
    $this->url = $params->url;
    $this->result = $params->record;

    $this->succeeded = $this->state === RecordState::Finished;
    $this->failed = $this->state === RecordState::NoInput;
  }
}
