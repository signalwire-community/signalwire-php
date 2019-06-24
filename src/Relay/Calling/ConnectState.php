<?php

namespace SignalWire\Relay\Calling;

final class ConnectState {
  const Disconnected = 'disconnected';
  const Connecting = 'connecting';
  const Connected = 'connected';
  const Failed = 'failed';

  private function __construct() {
    throw new Exception('Invalid class ConnectState');
  }
}
