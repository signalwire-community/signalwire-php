<?php

namespace SignalWire\Relay\Calling;

final class CallType {
  const Phone = 'phone';
  const Agora = 'agora';
  const Sip = 'sip';
  const WebRTC = 'webrtc';

  private function __construct() {
    throw new Exception('Invalid class CallType');
  }
}
