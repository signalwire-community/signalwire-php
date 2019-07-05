<?php

namespace SignalWire\Relay\Calling\Results;

use SignalWire\Relay\Calling\Components\Record;

class RecordResult extends BaseResult {

  public function __construct(Record $component) {
    parent::__construct($component);
  }

  public function getUrl() {
    return $this->component->url;
  }

  public function getDuration() {
    return $this->component->duration;
  }

  public function getSize() {
    return $this->component->size;
  }

}
