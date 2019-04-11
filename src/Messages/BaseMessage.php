<?php
namespace SignalWire\Messages;

abstract class BaseMessage {
  public $request = array();
  public $id;
  protected $method;

  protected function buildRequest($params){
    if (!$this->id) {
      $this->id = \SignalWire\Util\UUID::v4();
    }

    $this->request = array_merge(
      array('jsonrpc' => '2.0', 'id' => $this->id),
      $params
    );
  }

  public function toJson(Bool $pretty = false){
    return $pretty ? json_encode($this->request, JSON_PRETTY_PRINT) : json_encode($this->request);
  }
}
