<?php

require_once dirname(__FILE__) . '/../BaseRelayCase.php';

use SignalWire\Handler;
use SignalWire\Util\Events;

class RelayMessagingTest extends BaseRelayCase
{
  public function testOnReceive(): void {
    $response = json_decode('{"requester_nodeid":"uuid","responder_nodeid":"uuid","result":{"code":"200","message":"Receiving all inbound related to the requested relay contexts"}}');
    $this->_mockResponse([$response]);

    $mock = $this->getMockBuilder(\stdClass::class)->setMethods(['foo'])->getMock();
    $mock->expects($this->once())->method('foo');

    $this->client->messaging->onReceive(['home', 'office'], [$mock, 'foo'])->done(function() {
      $this->assertTrue(Handler::isQueued('relay-proto', 'messaging.ctxReceive.home'));
      $this->assertTrue(Handler::isQueued('relay-proto', 'messaging.ctxReceive.office'));

      $msg = json_decode('{"jsonrpc":"2.0","id":"req-uuid","method":"blade.broadcast","params":{"broadcaster_nodeid":"uuid","protocol":"relay-proto","channel":"notifications","event":"queuing.relay.messaging","params":{"event_type":"messaging.receive","space_id":"uuid","project_id":"uuid","context":"home","params":{"message_id":"id","context":"home","direction":"inbound","tags":["message","inbound","SMS","home","+1xxx","+1yyy","relay-client"],"from_number":"+1xxx","to_number":"+1yyy","body":"Welcome at SignalWire!","media":[],"segments":1,"message_state":"received"}}}}');
      Handler::trigger(Events::SocketMessage, $msg, $this->client->uuid);
    });
  }

  public function testOnStateChange(): void {
    $response = json_decode('{"requester_nodeid":"uuid","responder_nodeid":"uuid","result":{"code":"200","message":"Receiving all inbound related to the requested relay contexts"}}');
    $this->_mockResponse([$response]);

    $mock = $this->getMockBuilder(\stdClass::class)->setMethods(['foo'])->getMock();
    $mock->expects($this->once())->method('foo');

    $this->client->messaging->onStateChange(['home', 'office'], [$mock, 'foo'])->done(function() {
      $this->assertTrue(Handler::isQueued('relay-proto', 'messaging.ctxState.home'));
      $this->assertTrue(Handler::isQueued('relay-proto', 'messaging.ctxState.office'));

      $msg = json_decode('{"jsonrpc":"2.0","id":"req-id","method":"blade.broadcast","params":{"broadcaster_nodeid":"uuid","protocol":"relay-proto","channel":"notifications","event":"queuing.relay.messaging","params":{"event_type":"messaging.state","space_id":"uuid","project_id":"uuid","context":"office","params":{"message_id":"224d1192-b266-4ca2-bd8e-48c64a44d830","context":"office","direction":"outbound","tags":["message","outbound","SMS","office","relay-client"],"from_number":"+1xxx","to_number":"+1yyy","body":"Welcome at SignalWire!","media":[],"segments":1,"message_state":"queued"}}}}');
      Handler::trigger(Events::SocketMessage, $msg, $this->client->uuid);
    });
  }

  public function testSendWithSuccess(): void {
    $response = json_decode('{"requester_nodeid":"uuid","responder_nodeid":"uuid","result":{"message":"Message accepted","code":"200","message_id":"2c0e265d-4597-470e-9d5d-00581e0874a2"}}');
    $this->_mockResponse([$response]);

    $params = [ 'context' => 'office', 'from' => '8992222222', 'to' => '8991111111', 'body' => 'Hello' ];
    $this->client->messaging->send($params)->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Messaging\SendResult', $result);
      $this->assertTrue($result->successful);
      $this->assertEquals($result->getMessageId(), '2c0e265d-4597-470e-9d5d-00581e0874a2');
      $this->assertTrue($result->isSuccessful());
    });
  }

  public function testSendWithFailure(): void {
    $response = json_decode('{"requester_nodeid":"uuid","responder_nodeid":"uuid","result":{"message":"Some error","code":"400"}}');
    $this->_mockResponse([$response]);

    $params = [ 'context' => 'office', 'from' => '8992222222', 'to' => '8991111111', 'body' => 'Hello' ];
    $this->client->messaging->send($params)->done(function($result) {
      $this->assertInstanceOf('SignalWire\Relay\Messaging\SendResult', $result);
      $this->assertFalse($result->successful);
      $this->assertFalse($result->isSuccessful());
    });
  }
}
