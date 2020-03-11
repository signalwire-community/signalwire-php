<?php

require_once dirname(__FILE__) . '/BaseRelayCase.php';

use SignalWire\Handler;
use SignalWire\Messages\Connect;
use SignalWire\Messages\Execute;
use SignalWire\Messages\Subscription;
use SignalWire\Util\Events;

class RelayClientTest extends BaseRelayCase
{
  public function testSubscribeWithSuccessResponse(): void {
    $this->_mockResponse(json_decode('{"protocol":"proto","command":"add","subscribe_channels":["c1","c2"]}'));

    $this->client->subscribe('proto', array('c1', 'c2'));

    $this->assertArrayHasKey('protoc1', $this->client->subscriptions);
    $this->assertArrayHasKey('protoc2', $this->client->subscriptions);
  }

  public function testSubscribeWithFailedResponse(): void {
    $this->_mockResponse(json_decode('{"protocol":"proto","command":"add","failed_channels":["c1","c2"]}'));
    $this->client->subscribe('proto', array('c1', 'c2'));

    $this->assertCount(0, $this->client->subscriptions);
  }

  public function testSubscribeWithBothResponse(): void {
    $this->_mockResponse(json_decode('{"protocol":"proto","command":"add","subscribe_channels":["c1"],"failed_channels":["c2"]}'));
    $this->client->subscribe('proto', array('c1', 'c2'));

    $this->assertArrayHasKey('protoc1', $this->client->subscriptions);
    $this->assertArrayNotHasKey('protoc2', $this->client->subscriptions);
  }

  public function testSubscribeWithHandler(): void {
    $this->_mockResponse(json_decode('{"protocol":"proto","command":"add","subscribe_channels":["notifications"]}'));
    $fn = function($data) {};
    $this->client->subscribe('proto', array('notifications'), $fn);

    $this->assertArrayHasKey('protonotifications', $this->client->subscriptions);
    $this->assertTrue(SignalWire\Handler::isQueued('proto', 'notifications'));
    $this->assertEquals(SignalWire\Handler::queueCount('proto', 'notifications'), 1);
  }

  public function testCallingProperty(): void {
    $this->assertInstanceOf('SignalWire\Relay\Calling\Calling', $this->client->calling);
  }

  public function testTaskingProperty(): void {
    $this->assertInstanceOf('SignalWire\Relay\Tasking\Tasking', $this->client->tasking);
  }

  public function testMessagingProperty(): void {
    $this->assertInstanceOf('SignalWire\Relay\Messaging\Messaging', $this->client->messaging);
  }

  public function testOnSocketOpenWithSuccess(): void {
    $mockOnReady = $this->getMockBuilder(\stdClass::class)
      ->setMethods(['__invoke'])
      ->getMock();
    $mockOnReady->expects($this->once())->method('__invoke');
    $this->client->on('signalwire.ready', $mockOnReady);

    $requests = [
      new Connect('project', 'token'),
      new Execute(['protocol' => 'signalwire', 'method' => 'setup', 'params' => new \stdClass]),
      new Subscription([
        'command' => 'add',
        'protocol' => 'signalwire_service_random_uuid',
        'channels' => ['notifications']
      ])
    ];
    $responses = [
      json_decode('{"session_restored":false,"sessionid":"bfb34f66-3caf-45a9-8a4b-a74bbd3d0b28","nodeid":"uuid","master_nodeid":"uuid","authorization":{"project":"uuid","expires_at":null,"scopes":["calling","messaging","tasking"],"signature":"uuid-signature"},"routes":[],"protocols":[],"subscriptions":[],"authorities":[],"authorizations":[],"accesses":[],"protocols_uncertified":["signalwire"]}'),
      json_decode('{"result":{"protocol":"signalwire_service_random_uuid"}}'),
      json_decode('{"command":"add","failed_channels":[],"protocol":"signalwire_service_random_uuid","subscribe_channels":["notifications"]}')
    ];
    $this->_mockResponse($responses, $requests);

    Handler::trigger(Events::SocketOpen, null, $this->client->uuid);
    $this->assertEquals($this->client->sessionid, 'bfb34f66-3caf-45a9-8a4b-a74bbd3d0b28');
    $this->assertEquals($this->client->nodeid, 'uuid');
    $this->assertEquals($this->client->signature, 'uuid-signature');
    $this->assertEquals($this->client->relayProtocol, 'signalwire_service_random_uuid');
  }

  public function testOnSocketOpenOnTimeout(): void {
    $mockOnReady = $this->getMockBuilder(\stdClass::class)
      ->setMethods(['__invoke'])
      ->getMock();
    $mockOnReady->expects($this->never())->method('__invoke');
    $this->client->on('signalwire.ready', $mockOnReady);


    $stub = $this->createMock(SignalWire\Relay\Connection::class, ['send']);
    $stub->expects($this->once())
      ->method('send')
      ->with(new Connect('project', 'token'))
      ->will($this->returnValue(\React\Promise\reject(json_decode('{"code":-32000,"message":"Timeout"}'))));
    $this->client->connection = $stub;
    Handler::trigger(Events::SocketOpen, null, $this->client->uuid);
    $this->assertNull($this->client->connection);
    $this->assertTrue($this->client->idle);
    $this->assertTrue($this->client->autoReconnect);
  }
}
