<?php

namespace SignalWire\Relay\Calling;

final class PromptType {
  const Digits = 'digits';
  const Speech = 'speech';

  private function __construct() {
    throw new Exception('Invalid class PromptType');
  }
}
