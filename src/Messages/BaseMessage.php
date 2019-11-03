<?php
namespace SignalWire\Messages;
use Ramsey\Uuid\Uuid;

abstract class BaseMessage {
  public $request = array();
  public $id;
  protected $method;

  protected function buildRequest($params){
    if (!$this->id) {
      $this->id = Uuid::uuid4()->toString();
    }

    $this->request = array_merge(
      array('jsonrpc' => '2.0', 'id' => $this->id),
      $params
    );
  }

  public function toJson(Bool $pretty = false){
    return $pretty ? json_encode($this->request, JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES) : json_encode($this->request, JSON_UNESCAPED_SLASHES);
  }
}
