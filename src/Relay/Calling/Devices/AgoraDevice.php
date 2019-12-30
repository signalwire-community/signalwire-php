<?php

namespace SignalWire\Relay\Calling\Devices;

use SignalWire\Relay\Calling\CallType;

class AgoraDevice extends BaseDevice {
  public $type = CallType::Agora;
  public $params;

  protected function _buildParams($options) {
    $this->params = (object) [
      'from' => $options->from,
      'to' => $options->to,
      'appid' => $options->app_id,
      'channel' => $options->channel
    ];
    $this->_addTimeout($options);
  }

  public function from() {
    return $this->params->from;
  }

  public function to() {
    return $this->params->to;
  }
}
