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

  /**
   * Contexts to listen on
   * @var Array
   */
  public $contexts = [];

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

  public function onIncomingMessage($message): Coroutine {
    yield;
  }

  public function onMessageStateChange($message): Coroutine {
    yield;
  }

  public function onTask($message): Coroutine {
    yield;
  }

  public final function run() {
    $this->setup();
    $this->_checkRequirements();

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
        $success = yield Setup::receive($client, $this->contexts);
        if ($success) {
          yield $this->_registerCallingContexts();
          yield $this->_registerTaskingContexts();
          yield $this->_registerMessagingContexts();
          yield $this->ready();
        }
      } catch (\Throwable $th) {
        Log::error($th->getMessage());
        throw $th;
      }
    }));

    yield $this->client->connect();
  }

  private function _registerCallingContexts(): Coroutine {
    $callback = yield Recoil::callback(function ($call) {
      try {
        yield $this->onIncomingCall($call);
      } catch (\Throwable $error) {
        echo PHP_EOL;
        echo PHP_EOL . $error->getMessage();
        echo PHP_EOL . $error->getTraceAsString() . PHP_EOL;
      }
    });

    yield $this->client->calling->onReceive($this->contexts, $callback);
  }

  private function _registerTaskingContexts(): Coroutine {
    $callback = yield Recoil::callback(function ($message) {
      try {
        yield $this->onTask($message);
      } catch (\Throwable $error) {
        echo PHP_EOL;
        echo PHP_EOL . $error->getMessage();
        echo PHP_EOL . $error->getTraceAsString() . PHP_EOL;
      }
    });

    yield $this->client->tasking->onReceive($this->contexts, $callback);
  }

  private function _registerMessagingContexts(): Coroutine {
    $receiveCallback = yield Recoil::callback(function ($message) {
      try {
        yield $this->onIncomingMessage($message);
      } catch (\Throwable $error) {
        echo PHP_EOL;
        echo PHP_EOL . $error->getMessage();
        echo PHP_EOL . $error->getTraceAsString() . PHP_EOL;
      }
    });

    $changeStateCallback = yield Recoil::callback(function ($message) {
      try {
        yield $this->onMessageStateChange($message);
      } catch (\Throwable $error) {
        echo PHP_EOL;
        echo PHP_EOL . $error->getMessage();
        echo PHP_EOL . $error->getTraceAsString() . PHP_EOL;
      }
    });

    yield $this->client->messaging->onReceive($this->contexts, $receiveCallback);
    yield $this->client->messaging->onStateChange($this->contexts, $changeStateCallback);
  }

  private function _checkRequirements() {
    if (!isset($this->project)) {
      throw new \InvalidArgumentException(get_class($this) . ' must have a $project.');
    }
    if (!isset($this->token)) {
      throw new \InvalidArgumentException(get_class($this) . ' must have a $token.');
    }
    if (!isset($this->contexts) || !count($this->contexts)) {
      throw new \InvalidArgumentException(get_class($this) . ' must have one or more $contexts.');
    }
  }
}
