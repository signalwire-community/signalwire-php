<?php

namespace SignalWire\Relay\Calling\Results;

use SignalWire\Relay\Calling\Components\Answer;

class AnswerResult extends BaseResult {

  public function __construct(Answer $component) {
    parent::__construct($component);
  }

}
