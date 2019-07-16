<?php

namespace SignalWire\Relay\Calling;

final class DetectState {
  const Error = 'error';
  const Finished = 'finished';

  private function __construct() {
    throw new Exception('Invalid class DetectState');
  }
}
