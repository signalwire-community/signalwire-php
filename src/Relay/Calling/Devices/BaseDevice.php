<?php

namespace SignalWire\Relay\Calling\Devices;

interface DeviceInterface {
  public function from();
  public function to();
}

abstract class BaseDevice implements DeviceInterface {
  public $type;
  public $params = [];

  protected function _addTimeout(Array $options) {
    if (isset($options['timeout'])) {
      $this->params['timeout'] = $options['timeout'];
    }
  }
}
