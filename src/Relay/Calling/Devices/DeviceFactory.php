<?php

namespace SignalWire\Relay\Calling\Devices;

use SignalWire\Log;
use SignalWire\Relay\Calling\CallType;

class DeviceFactory {
  public static function create($options) {
    if (!is_object($options)) {
      $options = (object) $options;
    }
    switch ($options->type) {
      case CallType::Phone:
        return new PhoneDevice($options);
      case CallType::Agora:
        return new AgoraDevice($options);
      case CallType::Sip:
        return new SipDevice($options);
      case CallType::WebRTC:
        return new WebRTCDevice($options);
      default:
        Log::error("Unknown device type: {$options->type}");
        return null;
    }
  }
}
