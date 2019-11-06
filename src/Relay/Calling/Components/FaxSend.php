<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\Method;

class FaxSend extends BaseFax {
  public $method = Method::SendFax;
  private $_document;
  private $_identity;
  private $_header;

  public function __construct(Call $call, $document, string $identity = null, string $header = null) {
    parent::__construct($call);

    $this->_document = $document;
    $this->_identity = $identity;
    $this->_header = $header;
  }

  public function payload() {
    $payload = [
      'node_id' => $this->call->nodeId,
      'call_id' => $this->call->id,
      'control_id' => $this->controlId,
      'document' => $this->_document
    ];
    if ($this->_identity) {
      $payload['identity'] = $this->_identity;
    }
    if ($this->_header) {
      $payload['header_info'] = $this->_header;
    }
    return $payload;
  }
}
