<?php

namespace SignalWire\Relay\Calling;

final class CallState {
  const STATES = [CallState::None, CallState::Created, CallState::Ringing, CallState::Answered, CallState::Ending, CallState::Ended];

  const None = 'none';
  const Created = 'created';
  const Ringing = 'ringing';
  const Answered = 'answered';
  const Ending = 'ending';
  const Ended = 'ended';

  private function __construct() {
    throw new Exception('Invalid class CallState');
  }
}
