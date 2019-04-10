<?php
namespace SignalWire\Messages;

class Execute extends BaseMessage {

  protected $method = 'blade.execute';

  public function __construct(Array $params){
    $this->buildRequest(array(
      'method' => $this->method,
      'params' => $params
    ));
  }
}
