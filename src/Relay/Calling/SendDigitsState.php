<?php

namespace SignalWire\Relay\Calling;

final class SendDigitsState {
  const Finished = 'finished';

  private function __construct() {
    throw new Exception('Invalid class SendDigitsState');
  }
}
