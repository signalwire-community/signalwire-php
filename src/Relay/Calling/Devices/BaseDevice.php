<?php

namespace SignalWire\Relay\Calling\Devices;

abstract class BaseDevice {
  public $type;
  public $params;

  abstract public function from();
  abstract public function to();
  abstract protected function _buildParams($options);

  public function __construct($options) {
    if (isset($options->params)) {
      $this->params = $options->params;
    } else {
      $this->_buildParams((object) $options);
    }
  }

  protected function _addTimeout($options) {
    if (isset($options->timeout)) {
      $this->params->timeout = $options->timeout;
    }
  }
}
