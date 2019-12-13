<?php

namespace SignalWire\Relay\Calling\Devices;

use SignalWire\Relay\Calling\CallType;

class Sip extends BaseDevice {
  public $type = CallType::Sip;
  public $params = [];

  public function __construct(Array $options) {
    $this->params = [
      'from' => $options['from'],
      'to' => $options['to']
    ];
    if (isset($options['headers'])) {
      $this->params['headers'] = $options['headers'];
    }
    if (isset($options['codecs'])) {
      $this->params['codecs'] = $options['codecs'];
    }
    if (isset($options['webrtc_media']) && is_bool($options['webrtc_media'])) {
      $this->params['webrtc_media'] = $options['webrtc_media'];
    }
  }
}
