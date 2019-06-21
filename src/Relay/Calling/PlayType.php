<?php

namespace SignalWire\Relay\Calling;

final class PlayType {
  const Audio = 'audio';
  const TTS = 'tts';
  const Silence = 'silence';

  private function __construct() {
    throw new Exception('Invalid class PlayType');
  }
}
