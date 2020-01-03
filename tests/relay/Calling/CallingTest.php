<?php

require_once dirname(__FILE__) . '/../BaseRelayCase.php';

use SignalWire\Handler;

class RelayCallingTest extends BaseRelayCase
{
  public function testOnReceive(): void {
    $response = json_decode('{"requester_nodeid":"uuid","responder_nodeid":"uuid","result":{"code":"200","message":"Receiving all inbound related to the requested relay contexts"}}');
    $this->_mockResponse([$response]);

    $callback = function() {};
    $this->client->calling->onReceive(['c1', 'c2'], $callback)->done(function() {
      $this->assertTrue(Handler::isQueued('relay-proto', 'calling.ctxReceive.c1'));
      $this->assertTrue(Handler::isQueued('relay-proto', 'calling.ctxReceive.c2'));
    });
  }

  public function testOnReceiveAPhoneCall(): void {
    $response = json_decode('{"requester_nodeid":"uuid","responder_nodeid":"uuid","result":{"code":"200","message":"Receiving all inbound related to the requested relay contexts"}}');
    $this->_mockResponse([$response]);
    $mock = $this->createPartialMock(\stdClass::class, ['__invoke']);
    $mock->expects($spy = $this->once())->method('__invoke')->with($this->isInstanceOf('SignalWire\Relay\Calling\Call'));
    $this->client->calling->onReceive(['office'], $mock);
    $payload = json_decode('{"event_type":"calling.call.receive","params":{"call_state":"created","context":"office","device":{"type":"phone","params":{"from_number":"+12029999999","to_number":"+12028888888"}},"direction":"inbound","call_id":"call-id","node_id":"node-id"},"context":"office"}');
    $this->client->calling->notificationHandler($payload);
    $call = $spy->getInvocations()[0]->getParameters()[0];
    $this->assertEquals($call->type, 'phone');
    $this->assertEquals($call->from, '+12029999999');
    $this->assertEquals($call->to, '+12028888888');
  }

  public function testOnReceiveAnAgoraCall(): void {
    $response = json_decode('{"requester_nodeid":"uuid","responder_nodeid":"uuid","result":{"code":"200","message":"Receiving all inbound related to the requested relay contexts"}}');
    $this->_mockResponse([$response]);
    $mock = $this->createPartialMock(\stdClass::class, ['__invoke']);
    $mock->expects($spy = $this->once())->method('__invoke')->with($this->isInstanceOf('SignalWire\Relay\Calling\Call'));
    $this->client->calling->onReceive(['office'], $mock);
    $payload = json_decode('{"event_type":"calling.call.receive","params":{"call_state":"created","context":"office","device":{"type":"agora","params":{"from":"agora-from","to":"agora-to"}},"direction":"inbound","call_id":"call-id","node_id":"node-id"},"context":"office"}');
    $this->client->calling->notificationHandler($payload);
    $call = $spy->getInvocations()[0]->getParameters()[0];
    $this->assertEquals($call->type, 'agora');
    $this->assertEquals($call->from, 'agora-from');
    $this->assertEquals($call->to, 'agora-to');
  }

  public function testNewCallReturnsACallObject(): void {
    $call = $this->client->calling->newCall(['type' => 'phone', 'from' => '1234', 'to' => '5678']);
    $this->assertInstanceOf('SignalWire\Relay\Calling\Call', $call);
    $this->assertInstanceOf('SignalWire\Relay\Calling\Devices\PhoneDevice', $call->targets[0][0]);
    $this->assertEquals(count($call->targets), 1);

    $this->stateNotificationCreated = json_decode('{"event_type":"calling.call.state","params":{"call_state":"created","direction":"inbound","device":{"type":"phone","params":{"from_number":"+1234","to_number":"15678"}},"tag":"' . self::UUID . '","call_id":"call-id","node_id":"node-id"}}');
    $this->client->calling->notificationHandler($this->stateNotificationCreated);
    $this->assertEquals(count($call->attemptedDevices), 1);
    $this->stateNotificationAnswered = json_decode('{"event_type":"calling.call.state","params":{"call_state":"answered","direction":"inbound","device":{"type":"phone","params":{"from_number":"+1234","to_number":"15678"}},"call_id":"call-id","node_id":"node-id"}}');
    $this->client->calling->notificationHandler($this->stateNotificationAnswered);

    $this->assertInstanceOf('SignalWire\Relay\Calling\Devices\PhoneDevice', $call->device);
    $this->assertEquals($call->type, 'phone');
    $this->assertEquals($call->from, '+1234');
    $this->assertEquals($call->to, '15678');
  }
}
