<?php

namespace SignalWire\Relay\Calling;

final class RecordState {
  const Recording = 'recording';
  const NoInput = 'no_input';
  const Finished = 'finished';

  private function __construct() {
    throw new Exception('Invalid class RecordState');
  }
}
