<?php

namespace SignalWire\Relay;

use Generator as Coroutine;

abstract class Consumer {

  // abstract function setup(): Coroutine;
  // abstract function tearDown(): Coroutine;

  protected $loop = null;
  protected $client = null;
  private $_kernel = null;

  function __construct() {
    if (!isset($this->spaceUrl)) {
      throw new LogicException(get_class($this) . ' must have a $spaceUrl.');
    }
    if (!isset($this->project)) {
      throw new LogicException(get_class($this) . ' must have a $project.');
    }
    if (!isset($this->token)) {
      throw new LogicException(get_class($this) . ' must have a $token.');
    }
  }

  public final function run() {
    $this->loop = \React\EventLoop\Factory::create();
    $this->_kernel = \Recoil\React\ReactKernel::create($this->loop);
    $this->_kernel->execute([$this, '_init']);
    $this->loop->run();

    echo PHP_EOL . "TURN DOWN?" . PHP_EOL;
  }

  public function _init(): Coroutine {
    $this->client = new Client([
      'host' => $this->spaceUrl,
      'project' => $this->project,
      'token' => $this->token,
      'eventLoop' => yield \Recoil\Recoil::eventLoop()
    ]);

    $this->client->on('signalwire.error', function($error) {
      print_r($error);
    });

    $this->client->on('signalwire.ready', function($client) {
      $this->_kernel->execute(function(): Coroutine {
        try {
          yield $this->_registerCallingContexts();
          if (method_exists($this, 'setup')) {
            yield $this->setup();
          }
        } catch (\Throwable $th) {
          echo PHP_EOL . $th->getMessage() . PHP_EOL;
          throw $th;
        }
      });
    });

    yield $this->client->connect();
  }

  private function _registerCallingContexts(): Coroutine {
    if (!property_exists($this, 'contexts')) {
      return false;
    }
    if (!method_exists($this, 'onIncomingCall')) {
      throw new LogicException(get_class($this) . ' missing onIncomingCall() method to handle incoming calls.');
    }
    $promises = [];
    foreach ((array)$this->contexts as $context) {
      $promises[] = $this->client->calling->onInbound($context, yield \Recoil\Recoil::callback([$this, 'onIncomingCall']));
    }
    $results = yield $promises;
    foreach ($results as $res) {
      echo PHP_EOL . $res->message . PHP_EOL;
    }
    return $results;
  }
}
