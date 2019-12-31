<?php

namespace SignalWire\Relay\Calling\Devices;

use SignalWire\Relay\Calling\CallType;

class SipDevice extends BaseDevice {
  public $type = CallType::Sip;
  public $params;

  protected function _buildParams($options) {
    $this->params = (object) [
      'from' => $options->from,
      'to' => $options->to
    ];
    if (isset($options->headers)) {
      $this->params->headers = (object) $options->headers;
    }
    if (isset($options->codecs)) {
      $this->params->codecs = $options->codecs;
    }
    if (isset($options->webrtc_media) && is_bool($options->webrtc_media)) {
      $this->params->webrtc_media = $options->webrtc_media;
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
