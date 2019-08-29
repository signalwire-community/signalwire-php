<?php

namespace SignalWire\Relay\Calling;

final class RecordType {
  const Audio = 'audio';

  private function __construct() {
    throw new Exception('Invalid class RecordType');
  }
}
