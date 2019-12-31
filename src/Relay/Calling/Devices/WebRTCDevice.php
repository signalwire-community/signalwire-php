<?php

namespace SignalWire\Relay\Calling\Devices;

use SignalWire\Relay\Calling\CallType;

class WebRTCDevice extends BaseDevice {
  public $type = CallType::WebRTC;
  public $params;

  protected function _buildParams($options) {
    $this->params = (object) [
      'from' => $options->from,
      'to' => $options->to
    ];
    if (isset($options->codecs)) {
      $this->params->codecs = $options->codecs;
    }
    $this->_addTimeout($options);
  }

  public function from() {
    return $this->params->from;
  }

  public function to() {
    return $this->params->to;
  }
}
