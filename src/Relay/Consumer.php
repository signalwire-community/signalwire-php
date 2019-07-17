<?php

namespace SignalWire\Relay;

use Generator as Coroutine;
use SignalWire\Log;
use Recoil\Recoil;
use Recoil\React\ReactKernel;
use React\EventLoop\LoopInterface;
use React\EventLoop\Factory as ReactFactory;

abstract class Consumer {
  /**
   * SignalWire Space Url
   * @var String
   */
  public $host;

  /**
   * SignalWire project
   * @var String
   */
  public $project;

  /**
   * SignalWire token
   * @var String
   */
  public $token;

  protected $loop = null;
  protected $client = null;
  private $_kernel = null;

  public static function callback(Callable $cb) {
    return Recoil::callback($cb);
  }

  public function setup() {
  }

  public function ready(): Coroutine {
    yield;
  }

  public function teardown(): Coroutine {
    yield;
  }

  public function onIncomingCall($call): Coroutine {
    yield;
  }

  public function onTask($message): Coroutine {
    yield;
  }

  public final function run() {
    $this->setup();
    $this->_checkProjectAndToken();

    if (!($this->loop instanceof LoopInterface)) {
      $this->loop = ReactFactory::create();
    }
    $this->_kernel = ReactKernel::create($this->loop);
    $this->_kernel->execute([$this, '_init']);
    $this->loop->run();
    ReactKernel::start(function() {
      yield $this->teardown();
    });
  }

  public function _init(): Coroutine {
    $this->client = new Client([
      'host' => $this->host,
      'project' => $this->project,
      'token' => $this->token,
      'eventLoop' => yield \Recoil\Recoil::eventLoop()
    ]);

    $this->client->on('signalwire.ready', yield Recoil::callback(function($client) {
      try {
        yield $this->_registerCallingContexts();
        yield $this->_registerTaskingContexts();
        yield $this->ready();
      } catch (\Throwable $th) {
        Log::error($th->getMessage());
        throw $th;
      }
    }));

    yield $this->client->connect();
  }

  private function _registerCallingContexts(): Coroutine {
    if (!property_exists($this, 'contexts')) {
      return false;
    }

    $callback = yield Recoil::callback(function ($call) {
      try {
        yield $this->onIncomingCall($call);
      } catch (\Throwable $error) {
        echo PHP_EOL;
        echo PHP_EOL . $error->getMessage();
        echo PHP_EOL . $error->getTraceAsString() . PHP_EOL;
      }
    });

    yield $this->client->calling->onInbound((array)$this->contexts, $callback);
  }

  private function _registerTaskingContexts(): Coroutine {
    if (!property_exists($this, 'contexts')) {
      return false;
    }

    $callback = yield Recoil::callback(function ($message) {
      try {
        yield $this->onTask($message);
      } catch (\Throwable $error) {
        echo PHP_EOL;
        echo PHP_EOL . $error->getMessage();
        echo PHP_EOL . $error->getTraceAsString() . PHP_EOL;
      }
    });
    foreach ((array)$this->contexts as $context) {
      $this->client->tasking->onTask($context, $callback);
    }
  }

  private function _checkProjectAndToken() {
    if (!isset($this->project)) {
      throw new \InvalidArgumentException(get_class($this) . ' must have a $project.');
    }
    if (!isset($this->token)) {
      throw new \InvalidArgumentException(get_class($this) . ' must have a $token.');
    }
  }
}
