<?php

namespace SignalWire\Relay\Calling;

final class TapType {
  const Audio = 'audio';

  private function __construct() {
    throw new Exception('Invalid class TapType');
  }
}
