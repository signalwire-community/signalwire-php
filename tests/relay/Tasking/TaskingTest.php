<?php

require_once dirname(__FILE__) . '/../BaseRelayCase.php';

use SignalWire\Handler;

class RelayTaskingTest extends BaseRelayCase
{
  public function testRegisterContexts(): void {
    $response = json_decode('{"requester_nodeid":"uuid","responder_nodeid":"uuid","result":{"code":"200","message":"Receiving all inbound related to the requested relay contexts"}}');
    $this->_mockResponse([$response]);

    $callback = function() {};
    $this->client->tasking->registerContexts(['home', 'office'], $callback)->done(function() {
      $this->assertTrue(Handler::isQueued('relay-proto', 'tasking.context.home'));
      $this->assertTrue(Handler::isQueued('relay-proto', 'tasking.context.office'));
    });
  }
}
