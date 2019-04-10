<?php
namespace SignalWire\Messages;

abstract class BaseMessage {
  public $request = array();
  protected $method;

  protected function buildRequest($params){
    $this->request = array_merge(
      array('jsonrpc' => '2.0', 'id' => \SignalWire\Util\UUID::v4()),
      $params
    );
  }

  public function toJson(){
    return json_encode($this->request);
  }
}
