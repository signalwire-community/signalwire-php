<?php

namespace SignalWire\Relay\Calling\Results;

use SignalWire\Relay\Calling\Components\Play;

class PlayPauseResult extends BaseResult {

  public function __construct(Play $component) {
    parent::__construct($component);
  }

}
