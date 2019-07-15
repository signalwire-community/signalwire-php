<?php

namespace SignalWire\Relay\Calling\Results;

use SignalWire\Relay\Calling\Components\BaseFax;

class FaxResult extends BaseResult {

  public function __construct(BaseFax $component) {
    parent::__construct($component);
  }

  public function getDirection() {
    return $this->component->direction;
  }

  public function getIdentity() {
    return $this->component->identity;
  }

  public function getRemoteIdentity() {
    return $this->component->remoteIdentity;
  }

  public function getDocument() {
    return $this->component->document;
  }

  public function getPages() {
    return $this->component->pages;
  }

}
