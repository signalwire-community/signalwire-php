<?php

namespace SignalWire\Relay\Calling;

final class TapState {
  const Tapping = 'tapping';
  const Finished = 'finished';

  private function __construct() {
    throw new Exception('Invalid class TapState');
  }
}
