<?php

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Task;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ClientException;

class RelayTaskTest extends TestCase
{
  protected $task;

  protected function setUp() {
    $this->task = new Task('project', 'token');
  }

  public function tearDown() {
    unset($this->task);
  }

  private function _mockResponse($responses) {
    $mock = new MockHandler($responses);

    $handlerStack = HandlerStack::create($mock);
    $this->task->_httpClient = new Client(['handler' => $handlerStack]);
  }

  public function testDeliverWithSuccess(): void {
    $this->_mockResponse([
      new Response(204)
    ]);

    $success = $this->task->deliver('context', ['key' => 'value']);

    $this->assertTrue($success);
  }

  public function testDeliverWithException(): void {
    $this->_mockResponse([
      new ClientException('POST 400 Bad Request', new Request('POST', '/api/relay/rest/tasks'), new Response())
    ]);

    $success = $this->task->deliver('context', ['key' => 'value']);

    $this->assertFalse($success);
  }
}
