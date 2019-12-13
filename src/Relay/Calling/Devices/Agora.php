<?php

namespace SignalWire\Relay\Calling\Devices;

use SignalWire\Relay\Calling\CallType;

class Agora extends BaseDevice {
  public $type = CallType::Agora;
  public $params = [];

  public function __construct(Array $options) {
    $this->params = [
      'from' => $options['from'],
      'to' => $options['to'],
      'appid' => $options['app_id'],
      'channel' => $options['channel']
    ];
  }
}
