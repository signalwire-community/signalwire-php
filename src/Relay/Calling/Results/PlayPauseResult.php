<?php

namespace SignalWire\Relay\Calling\Results;

class PlayPauseResult {
  public $successful = false;

  public function __construct(bool $successful) {
    $this->successful = $successful;
  }
}
