<?php

require_once dirname(__FILE__) . '/BaseRelayCase.php';

use SignalWire\Relay\Setup;

class RelaySetupTest extends BaseRelayCase
{
  public function testProtocolSetup(): void {
    $responseProto = json_decode('{"requester_nodeid":"ad490dc4-550a-4742-929d-b86fdf8958ef","responder_nodeid":"b0007713-071d-45f9-88aa-302d14e1251c","result":{"protocol":"signalwire_calling_proto"}}');
    $responseSubscr = json_decode('{"protocol":"signalwire_calling_proto","command":"add","subscribe_channels":["notifications"]}');
    $this->_mockResponse([$responseProto, $responseSubscr]);

    Setup::protocol($this->client)->then(function (String $protocol) {
      $this->assertEquals('signalwire_calling_proto', $protocol);
      $this->assertArrayHasKey('signalwire_calling_protonotifications', $this->client->subscriptions);
    });
  }

  public function testReceiveWithInvalidData(): void {
    $this->_mockSendNotToBeCalled();

    Setup::receive($this->client, '')->done(function ($success) {
      $this->assertFalse($success);
    });

    Setup::receive($this->client, [])->done(function ($success) {
      $this->assertFalse($success);
    });

    Setup::receive($this->client, [''])->done(function ($success) {
      $this->assertFalse($success);
    });
  }

  public function testReceiveWithString(): void {
    $response = json_decode('{"requester_nodeid":"uuid","responder_nodeid":"uuid","result":{"code":"200","message":"Receiving all inbound related to the requested relay contexts"}}');
    $this->_mockResponse([$response]);

    Setup::receive($this->client, 'test')->done(function ($success) {
      $this->assertTrue($success);
      $this->assertEquals(['test'], $this->client->contexts);
    });
  }

  public function testReceiveWithArray(): void {
    $response = json_decode('{"requester_nodeid":"uuid","responder_nodeid":"uuid","result":{"code":"200","message":"Receiving all inbound related to the requested relay contexts"}}');
    $this->_mockResponse([$response]);

    Setup::receive($this->client, ['test1', 'test2'])->done(function ($success) {
      $this->assertTrue($success);
      $this->assertEquals(['test1', 'test2'], $this->client->contexts);
    });
  }

  public function testReceiveContextAlreadyRegistered(): void {
    $this->_mockSendNotToBeCalled();

    $this->client->contexts = ['exists'];
    Setup::receive($this->client, 'exists')->done(function ($success) {
      $this->assertTrue($success);
      $this->assertEquals(['exists'], $this->client->contexts);
    });
  }

  public function testReceiveMixedContextsAlreadyRegisteredAndNot(): void {
    $response = json_decode('{"requester_nodeid":"uuid","responder_nodeid":"uuid","result":{"code":"200","message":"Receiving all inbound related to the requested relay contexts"}}');
    $this->_mockResponse([$response]);

    $this->client->contexts = ['exists'];

    Setup::receive($this->client, ['exists', 'home', 'office'])->done(function ($success) {
      $this->assertTrue($success);
      $this->assertEquals(['exists', 'home', 'office'], $this->client->contexts);
    });
  }
}
