<?php
namespace SignalWire\Relay\Calling;

use SignalWire\Messages\Execute;
use Ramsey\Uuid\Uuid;
use SignalWire\Relay\Calling\Components;
use SignalWire\Relay\Calling\Actions;
use SignalWire\Relay\Calling\Results;

class Call {
  const DefaultTimeout = 30;
  public $id;
  public $nodeId;
  public $tag;
  public $relayInstance;
  public $prevState = CallState::None;
  public $state = CallState::None;
  public $peer;
  public $context = '';
  public $type = '';
  public $from = '';
  public $to = '';
  public $timeout = self::DefaultTimeout;

  public $answered = false;
  public $active = false;
  public $ended = false;
  public $failed = false;
  public $busy = false;

  private $_cbQueue = [];
  private $_components = [];

  public function __construct(Calling $relayInstance, $options) {
    $this->relayInstance = $relayInstance;

    $this->tag = Uuid::uuid4()->toString();
    if (isset($options->call_id)) {
      $this->id = $options->call_id;
    }
    if (isset($options->node_id)) {
      $this->nodeId = $options->node_id;
    }
    if (isset($options->context)) {
      $this->context = $options->context;
    }

    if (isset($options->device)) {
      if (isset($options->device->type)) {
        $this->type = $options->device->type;
      }
      if (isset($options->device->params->from_number)) {
        $this->from = $options->device->params->from_number;
      }
      if (isset($options->device->params->to_number)) {
        $this->to = $options->device->params->to_number;
      }
      if (isset($options->device->params->timeout)) {
        $this->timeout = $options->device->params->timeout;
      }
    }

    $this->relayInstance->addCall($this);
  }

  public function getDevice() {
    return [
      'type' => $this->type,
      'params' => [
        'from_number' => $this->from,
        'to_number' => $this->to,
        'timeout' => $this->timeout
      ]
    ];
  }

  public function dial() {
    $component = new Components\Dial($this);
    $this->_addComponent($component);

    $events = [CallState::Answered, CallState::Ending, CallState::Ended];
    return $component->_waitFor(...$events)->then(function() use (&$component) {
      return new Results\DialResult($component);
    });
  }

  public function hangup(String $reason = 'hangup') {
    $component = new Components\Hangup($this, $reason);
    $this->_addComponent($component);

    return $component->_waitFor(CallState::Ended)->then(function() use (&$component) {
      return new Results\HangupResult($component);
    });
  }

  public function answer() {
    $component = new Components\Answer($this);
    $this->_addComponent($component);

    $events = [CallState::Answered, CallState::Ending, CallState::Ended];
    return $component->_waitFor(...$events)->then(function() use (&$component) {
      return new Results\AnswerResult($component);
    });
  }

  public function record(Array $record) {
    $component = new Components\Record($this, $record);
    $this->_addComponent($component);

    return $component->_waitFor(RecordState::NoInput, RecordState::Finished)->then(function() use (&$component) {
      return new Results\RecordResult($component);
    });
  }

  public function recordAsync(Array $record) {
    $component = new Components\Record($this, $record);
    $this->_addComponent($component);

    return $component->execute()->then(function() use (&$component) {
      return new Actions\RecordAction($component);
    });
  }

  public function play(...$play) {
    $component = new Components\Play($this, $play);
    $this->_addComponent($component);

    return $component->_waitFor(PlayState::Error, PlayState::Finished)->then(function() use (&$component) {
      return new Results\PlayResult($component);
    });
  }

  public function playAsync(...$play) {
    $component = new Components\Play($this, $play);
    $this->_addComponent($component);

    return $component->execute()->then(function() use (&$component) {
      return new Actions\PlayAction($component);
    });
  }

  public function playAudio(String $url) {
    return $this->play(['type' => PlayType::Audio, 'params' => [ 'url' => $url ]]);
  }

  public function playAudioAsync(String $url) {
    return $this->playAsync(['type' => PlayType::Audio, 'params' => [ 'url' => $url ]]);
  }

  public function playSilence(String $duration) {
    return $this->play(['type' => PlayType::Silence, 'params' => [ 'duration' => $duration ]]);
  }

  public function playSilenceAsync(String $duration) {
    return $this->playAsync(['type' => PlayType::Silence, 'params' => [ 'duration' => $duration ]]);
  }

  public function playTTS(Array $options) {
    return $this->play(['type' => PlayType::TTS, 'params' => $options]);
  }

  public function playTTSAsync(Array $options) {
    return $this->playAsync(['type' => PlayType::TTS, 'params' => $options]);
  }

  public function on(String $event, Callable $fn) {
    $this->_cbQueue[$event] = $fn;
    return $this;
  }

  public function off(String $event, Callable $fn = null) {
    unset($this->_cbQueue[$event]);
    return $this;
  }

  public function _execute(Execute $msg) {
    return $this->relayInstance->client->execute($msg)->then(function($result) {
      return $result->result;
    }, function($error) {
      $e = isset($error->result) ? $error->result : $error;
      throw new \Exception($e->message, $e->code);
    });
  }

  public function _stateChange($params) {
    $this->prevState = $this->state;
    $this->state = $params->call_state;
    $this->_dispatchCallback('stateChange');
    $this->_dispatchCallback($params->call_state);
    $this->_notifyComponents(Notification::State, $this->tag, $params);
    if ($params->call_state === CallState::Ended) {
      $this->relayInstance->removeCall($this);
    }
  }

  public function _connectChange($params) {
    $this->_notifyComponents(Notification::Connect, $this->tag, $params);
    $this->_dispatchCallback("connect.stateChange");
    $this->_dispatchCallback("connect.$params->connect_state");
  }

  public function _recordChange($params) {
    $this->_notifyComponents(Notification::Record, $params->control_id, $params);
    $this->_dispatchCallback('record.stateChange', $params);
    $this->_dispatchCallback("record.$params->state", $params);
  }

  public function _playChange($params) {
    $this->_notifyComponents(Notification::Play, $params->control_id, $params);
    $this->_dispatchCallback('play.stateChange', $params);
    $this->_dispatchCallback("play.$params->state", $params);
  }

  public function _collectChange($params) {
    $this->_notifyComponents(Notification::Collect, $params->control_id, $params);
    $this->_dispatchCallback('collect', $params);
  }

  private function _dispatchCallback(string $key, ...$params) {
    if (isset($this->_cbQueue[$key]) && is_callable($this->_cbQueue[$key])) {
      call_user_func($this->_cbQueue[$key], $this, ...$params);
    }
  }

  private function _addComponent(Components\BaseComponent $component) {
    array_push($this->_components, $component);
  }

  private function _notifyComponents(String $eventType, String $controlId, $params) {
    foreach ($this->_components as $component) {
      if ($component->completed) {
        continue;
      }
      if ($controlId === $component->controlId && $eventType === $component->eventType) {
        $component->notificationHandler($params);
      }
    }
  }
}
