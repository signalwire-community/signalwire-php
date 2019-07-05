<?php

namespace SignalWire\Relay\Calling\Results;

use SignalWire\Relay\Calling\Components\Prompt;

class PromptResult extends BaseResult {

  public function __construct(Prompt $component) {
    parent::__construct($component);
  }

  public function getType() {
    return $this->component->type;
  }

  public function getResult() {
    return $this->component->input;
  }

  public function getTerminator() {
    return $this->component->terminator;
  }

  public function getConfidence() {
    return $this->component->confidence;
  }

}
