<?php

namespace SignalWire\Relay\Messaging;

class SendResult {

  public $event;
  public $successful;
  public $messageId;
  public $errors;

  public function __construct($result) {
    $this->successful = isset($result->code) && $result->code === '200';
    $this->messageId = isset($result->message_id) ? $result->message_id : null;
  }

  public function isSuccessful() {
    return $this->successful;
  }

  public function getEvent() {
    return $this->event;
  }

  public function getMessageId() {
    return $this->messageId;
  }
}
