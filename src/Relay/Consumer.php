<?php

namespace SignalWire\Relay;

use Generator as Coroutine;
use Recoil\Recoil;
use Recoil\React\ReactKernel;
use React\EventLoop\LoopInterface;
use React\EventLoop\Factory as ReactFactory;

abstract class Consumer {

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

  public function setup(): Coroutine {
    yield;
  }

  public function tearDown(): Coroutine {
    yield;
  }

  public final function run() {
    if (!($this->loop instanceof LoopInterface)) {
      $this->loop = ReactFactory::create();
    }
    $this->_kernel = ReactKernel::create($this->loop);
    $this->_kernel->execute([$this, '_init']);
    $this->loop->run();
    ReactKernel::start(function() {
      yield $this->tearDown();
    });
  }

  public function _init(): Coroutine {
    $this->client = new Client([
      'host' => $this->spaceUrl,
      'project' => $this->project,
      'token' => $this->token,
      'eventLoop' => yield \Recoil\Recoil::eventLoop()
    ]);

    $this->client->on('signalwire.error', function($error) {
      echo $error->getMessage();
    });

    $this->client->on('signalwire.ready', function($client) {
      $this->_kernel->execute(function(): Coroutine {
        try {
          yield $this->_registerCallingContexts();
          yield $this->setup();
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
      $promises[] = $this->client->calling->onInbound($context, yield Recoil::callback([$this, 'onIncomingCall']));
    }
    $results = yield $promises;
    foreach ($results as $res) {
      echo PHP_EOL . $res->message . PHP_EOL;
    }
    return $results;
  }
}
