<?php

namespace SignalWire\Relay\Calling;

final class Method {
  const Answer = 'calling.answer';
  const Begin = 'calling.begin';
  const Connect = 'calling.connect';
  const Disconnect = 'calling.disconnect';
  const End = 'calling.end';
  const Record = 'calling.record';
  const Play = 'calling.play';
  const PlayAndCollect = 'calling.play_and_collect';
  const ReceiveFax = 'calling.receive_fax';
  const SendFax = 'calling.send_fax';
  const Detect = 'calling.detect';
  const Tap = 'calling.tap';
  const SendDigits = 'calling.send_digits';

  private function __construct() {
    throw new Exception('Invalid class Method');
  }
}
