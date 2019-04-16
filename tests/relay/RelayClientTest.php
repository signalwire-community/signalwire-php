<?php

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Client;
use Clue\React\Block;

class RelayClientTest extends TestCase
{
  protected $client;
  private $_opts = array("host" => "host", "project" => "project", "token" => "token");

  // protected function setUp() {
  // }

  public function tearDown() {
    Mockery::close();
    unset($this->client);
  }

  private function initClient() {
    $this->client = new Client($this->_opts);
  }

  // Testing Subscribe
  public function testSubscribeWithSuccessResponse(): void {
    mockConnectionSend(json_decode('{"jsonrpc":"2.0","id":"1fa5cef5-9e20-4600-8ee1-ddb315c64cae","result":{"protocol":"proto","command":"add","subscribe_channels":["c1","c2"]}}'));
    $this->initClient();

    $promise = $this->client->subscribe('proto', array('c1', 'c2'));
    $result = Block\await($promise, getLoop());

    $this->assertArrayHasKey('protoc1', $this->client->subscriptions);
    $this->assertArrayHasKey('protoc2', $this->client->subscriptions);
  }

  public function testSubscribeWithFailedResponse(): void {
    mockConnectionSend(json_decode('{"jsonrpc":"2.0","id":"1fa5cef5-9e20-4600-8ee1-ddb315c64cae","result":{"protocol":"proto","command":"add","failed_channels":["c1","c2"]}}'));
    $this->initClient();

    $promise = $this->client->subscribe('proto', array('c1', 'c2'));
    $result = Block\await($promise, getLoop());

    $this->assertCount(0, $this->client->subscriptions);
  }

  public function testSubscribeWithBothResponse(): void {
    mockConnectionSend(json_decode('{"jsonrpc":"2.0","id":"1fa5cef5-9e20-4600-8ee1-ddb315c64cae","result":{"protocol":"proto","command":"add","subscribe_channels":["c1"],"failed_channels":["c2"]}}'));
    $this->initClient();

    $promise = $this->client->subscribe('proto', array('c1', 'c2'));
    $result = Block\await($promise, getLoop());

    $this->assertArrayHasKey('protoc1', $this->client->subscriptions);
    $this->assertArrayNotHasKey('protoc2', $this->client->subscriptions);
  }

  public function testSubscribeWithHandler(): void {
    mockConnectionSend(json_decode('{"jsonrpc":"2.0","id":"1fa5cef5-9e20-4600-8ee1-ddb315c64cae","result":{"protocol":"proto","command":"add","subscribe_channels":["notifications"]}}'));
    $this->initClient();

    $fn = function($data) {};
    $promise = $this->client->subscribe('proto', array('notifications'), $fn);
    $result = Block\await($promise, getLoop());

    $this->assertArrayHasKey('protonotifications', $this->client->subscriptions);
    $this->assertTrue(SignalWire\Handler::isQueued('proto', 'notifications'));
    $this->assertEquals(SignalWire\Handler::queueCount('proto', 'notifications'), 1);
  }
}
