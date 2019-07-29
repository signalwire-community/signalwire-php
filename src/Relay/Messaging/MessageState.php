<?php

namespace SignalWire\Relay\Messaging;

final class MessageState {
  const STATES = [ MessageState::Queued, MessageState::Initiated, MessageState::Sent, MessageState::Delivered, MessageState::Undelivered, MessageState::Failed ];

  const Queued = 'queued';
  const Initiated = 'initiated';
  const Sent = 'sent';
  const Delivered = 'delivered';
  const Undelivered = 'undelivered';
  const Failed = 'failed';

  private function __construct() {
    throw new Exception('Invalid class MessageState');
  }
}
