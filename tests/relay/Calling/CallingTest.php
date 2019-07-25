<?php

require_once dirname(__FILE__) . '/../BaseRelayCase.php';

use SignalWire\Handler;

class RelayCallingTest extends BaseRelayCase
{
  public function testRegisterContexts(): void {
    $response = json_decode('{"requester_nodeid":"uuid","responder_nodeid":"uuid","result":{"code":"200","message":"Receiving all inbound related to the requested relay contexts"}}');
    $this->_mockResponse([$response]);

    $callback = function() {};
    $this->client->calling->registerContexts(['c1', 'c2'], $callback)->done(function() {
      $this->assertTrue(Handler::isQueued('relay-proto', 'calling.context.c1'));
      $this->assertTrue(Handler::isQueued('relay-proto', 'calling.context.c2'));
    });
  }
}
