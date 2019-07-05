<?php

namespace SignalWire\Relay\Calling;

final class PromptState {
  const Error = 'error';
  const NoInput = 'no_input';
  const NoMatch = 'no_match';
  const Digit = 'digit';
  const Speech = 'speech';

  private function __construct() {
    throw new Exception('Invalid class PromptState');
  }
}
