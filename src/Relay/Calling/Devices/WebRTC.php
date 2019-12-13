<?php

namespace SignalWire\Relay\Calling\Devices;

use SignalWire\Relay\Calling\CallType;

class WebRTC extends BaseDevice {
  public $type = CallType::WebRTC;
  public $params = [];

  public function __construct(Array $options) {
    $this->params = [
      'from' => $options['from'],
      'to' => $options['to']
    ];
    if (isset($options['codecs'])) {
      $this->params['codecs'] = $options['codecs'];
    }
  }
}
