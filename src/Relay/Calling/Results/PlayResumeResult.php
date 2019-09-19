<?php

namespace SignalWire\Relay\Calling\Results;

use SignalWire\Relay\Calling\Components\Play;

class PlayResumeResult extends BaseResult {

  public function __construct(Play $component) {
    parent::__construct($component);
  }

}
