<?php
namespace SignalWire\Messages;

class Subscription extends BaseMessage {

  protected $method = 'blade.subscription';

  public function __construct(Array $params){
    $this->buildRequest(array(
      'method' => $this->method,
      'params' => $params
    ));
  }
}
