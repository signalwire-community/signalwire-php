<?php
namespace SignalWire\Relay\Messaging;

class Message {
  public $id;
  public $state;
  public $context;
  public $from;
  public $to;
  public $body;
  public $direction;
  public $media;
  public $segments;
  public $tags;
  public $reason;

  public function __construct($options) {

    if (isset($options->message_id)) {
      $this->id = $options->message_id;
    }
    if (isset($options->message_state)) {
      $this->state = $options->message_state;
    }
    if (isset($options->context)) {
      $this->context = $options->context;
    }
    if (isset($options->from_number)) {
      $this->from = $options->from_number;
    }
    if (isset($options->to_number)) {
      $this->to = $options->to_number;
    }
    if (isset($options->body)) {
      $this->body = $options->body;
    }
    if (isset($options->direction)) {
      $this->direction = $options->direction;
    }
    if (isset($options->media)) {
      $this->media = $options->media;
    }
    if (isset($options->segments)) {
      $this->segments = $options->segments;
    }
    if (isset($options->tags)) {
      $this->tags = $options->tags;
    }
    if (isset($options->reason)) {
      $this->reason = $options->reason;
    }
  }
}
