<?php

namespace SignalWire\Relay\Calling;

final class DetectState {
  const Error = 'error';
  const Finished = 'finished';
  const CED = 'CED';
  const CNG = 'CNG';
  const Machine = 'MACHINE';
  const Human = 'HUMAN';
  const Unknown = 'UNKNOWN';
  const Ready = 'READY';
  const NotReady = 'NOT_READY';

  private function __construct() {
    throw new Exception('Invalid class DetectState');
  }
}
