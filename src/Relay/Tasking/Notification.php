<?php

namespace SignalWire\Relay\Tasking;

final class Notification {
  const Receive = 'tasking.task.receive';

  private function __construct() {
    throw new Exception('Invalid class Notification');
  }
}
