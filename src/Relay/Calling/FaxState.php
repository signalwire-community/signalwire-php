<?php

namespace SignalWire\Relay\Calling;

final class FaxState {
  const Page = 'page';
  const Error = 'error';
  const Finished = 'finished';

  private function __construct() {
    throw new Exception('Invalid class FaxState');
  }
}
