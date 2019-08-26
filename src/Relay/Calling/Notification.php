<?php

namespace SignalWire\Relay\Calling;

final class Notification {
  const State = 'calling.call.state';
  const Connect = 'calling.call.connect';
  const Record = 'calling.call.record';
  const Play = 'calling.call.play';
  const Collect = 'calling.call.collect';
  const Receive = 'calling.call.receive';
  const Fax = 'calling.call.fax';
  const Detect = 'calling.call.detect';
  const Tap = 'calling.call.tap';
  const SendDigits = 'calling.call.send_digits';

  private function __construct() {
    throw new Exception('Invalid class Notification');
  }
}
