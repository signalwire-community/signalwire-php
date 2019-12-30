<?php

namespace SignalWire\Relay\Calling\Devices;

use SignalWire\Relay\Calling\CallType;

class PhoneDevice extends BaseDevice {
  public $type = CallType::Phone;
  public $params;

  protected function _buildParams($options) {
    $this->params = (object) [
      'from_number' => $options->from,
      'to_number' => $options->to
    ];
    $this->_addTimeout($options);
  }

  public function from() {
    return $this->params->from_number;
  }

  public function to() {
    return $this->params->to_number;
  }
}
