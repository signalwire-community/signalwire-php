<?php
namespace SignalWire\Relay\Calling;
use SignalWire\Messages\Execute;

class Call {
  const DefaultTimeout = 30;
  public $ready = false;
  public $prevState = '';
  public $state = '';
  public $prevConnectState = '';
  public $connectState = '';
  public $context;
  public $peer;
  public $device = array();
  public $type = '';
  public $from = '';
  public $to = '';
  public $timeout = self::DefaultTimeout;

  public function __construct(Calling $relayInstance, Object $options) {
    $this->device = $options->device;
    $this->type = $this->device->type;

    $this->from = isset($this->device->params->from_number) ? $this->device->params->from_number : $this->from;
    $this->to = isset($this->device->params->to_number) ? $this->device->params->to_number : $this->to;
    $this->timeout = isset($this->device->params->timeout) ? $this->device->params->timeout : $this->timeout;

    $this->tag = \SignalWire\Util\UUID::v4();
  }

  public function on(String $event, Callable $fn) {
    // TODO:
    return $this;
  }

  public function off(String $event, Callable $fn = null) {
    // TODO:
    return $this;
  }

  public function begin() {
    $msg = new Execute(array(
      'protocol' => '',
      'method' => 'call.begin',
      'params' => array(
        'tag' => $this->tag,
        'device' => $this->device
      )
    ));
  }

  public function hangup() {

  }
}
