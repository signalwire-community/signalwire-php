<?php

namespace SignalWire\Relay\Calling;

final class DisconnectReason {
  const Hangup = 'hangup';
  const Cancel = 'cancel';
  const Busy = 'busy';
  const NoAnswer = 'noAnswer';
  const Decline = 'decline';
  const Error = 'error';

  private function __construct() {
    throw new Exception( 'Invalid class DisconnectReason');
  }
}
