<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Call;
use SignalWire\Relay\Calling\Blocker;
use SignalWire\Messages\Execute;
use Ramsey\Uuid\Uuid;
use SignalWire\Relay\Calling\Event;

abstract class BaseComponent {

  /** Call this component belongs to */
  public $call;

  /** Type of Relay events to handle. state|play|collect|record|connect */
  public $eventType;

  /** Relay method to execute */
  public $method = null;

  /** ControlId to identify the component among the notifications */
  public $controlId;

  /** Blocker to wait some events */
  public $blocker;

  /** Current component state */
  public $state;

  /** Whether the component has complete the execution */
  public $completed = false;

  /** Whether the component has finished successfully */
  public $successful = false;

  /** The final event of the component */
  public $event;

  /** Relay response of the first execute. (200/400/500) */
  protected $_executeResult;

  /** Array of events to wait to resolve the Blocker */
  protected $_eventsToWait = [];

  /**
   * Constructor
   *
   */
  public function __construct(Call $call) {
    $this->call = $call;
    $this->controlId = Uuid::uuid4()->toString();
  }

  /**
   * Payload sent to Relay in requests
   *
   */
  abstract function payload();

  /**
   * Handle Relay notification to update the component
   *
   * @param params Relay notification params
   */
  abstract function notificationHandler($params);

  public function execute() {
    if ($this->call->ended) {
      $this->terminate();
      return \React\Promise\resolve();
    }
    if ($this->method === null) {
      return \React\Promise\resolve();
    }
    $msg = new Execute([
      'protocol' => $this->call->relayInstance->client->relayProtocol,
      'method' => $this->method,
      'params' => $this->payload()
    ]);

    return $this->call->_execute($msg)->then(function($result) {
      $this->_executeResult = $result;

      return $this->_executeResult;
    }, function($error) {
      $this->terminate();
    });
  }

  public function _waitFor(...$events) {
    $this->_eventsToWait = $events;
    $this->blocker = new Blocker($this->eventType, $this->controlId);

    return $this->execute()->then(function() {
      return $this->blocker->promise;
    });
  }

  public function terminate($params = null) {
    $this->completed = true;
    $this->successful = false;
    $this->state = 'failed';
    if ($params && isset($params->call_state)) {
      $this->event = new Event($params->call_state, $params);
    }
    if ($this->_hasBlocker()) {
      ($this->blocker->resolve)();
    }
  }

  protected function _hasBlocker() {
    return $this->blocker instanceof Blocker;
  }
}
