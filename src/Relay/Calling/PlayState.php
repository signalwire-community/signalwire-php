<?php

namespace SignalWire\Relay\Calling;

final class PlayState {
  const Playing = 'playing';
  const Error = 'error';
  const Finished = 'finished';

  private function __construct() {
    throw new Exception('Invalid class PlayState');
  }
}
