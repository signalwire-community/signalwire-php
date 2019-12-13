<?php

namespace SignalWire\Relay\Calling\Devices;

use SignalWire\Relay\Calling\CallType;

class Phone extends BaseDevice {
  public $type = CallType::Phone;
  public $params = [];

  public function __construct(Array $options) {
    $this->params = [
      'from_number' => $options['from'],
      'to_number' => $options['to']
    ];
  }

  public function from() {
    return $this->params['from_number'];
  }

  public function to() {
    return $this->params['to_number'];
  }
}
