<?php

namespace SignalWire\Relay\Calling\Results;

class StopResult {
  public $code;
  public $message;
  public $successful = false;

  public function __construct($result) {
    $this->code = $result->code;
    $this->message = $result->message;
    $this->successful = $this->code === '200';
  }
}
