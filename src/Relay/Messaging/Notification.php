<?php

namespace SignalWire\Relay\Messaging;

final class Notification {
  const State = 'messaging.state';
  const Receive = 'messaging.receive';

  private function __construct() {
    throw new Exception('Invalid class Notification');
  }
}
