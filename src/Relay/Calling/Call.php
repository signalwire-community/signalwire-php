<?php
namespace SignalWire\Relay\Calling;

use SignalWire\Messages\Execute;
use Ramsey\Uuid\Uuid;

class Call {
  const DefaultTimeout = 30;
  public $id = false;
  public $nodeId = false;
  public $relayInstance;
  public $prevState = '';
  public $state = '';
  public $prevConnectState = '';
  public $connectState = '';
  public $context = '';
  public $peer;
  public $device = array();
  public $type = '';
  public $from = '';
  public $to = '';
  public $timeout = self::DefaultTimeout;

  private $_cbQueue = array();
  private $_blockers = array();
  private $_actions = array();

  public function __construct(Calling $relayInstance, $options) {
    $this->relayInstance = $relayInstance;
    $this->device = $options->device;
    $this->type = $this->device->type;

    $this->from = isset($this->device->params->from_number) ? $this->device->params->from_number : $this->from;
    $this->to = isset($this->device->params->to_number) ? $this->device->params->to_number : $this->to;
    $this->timeout = isset($this->device->params->timeout) ? $this->device->params->timeout : $this->timeout;

    $this->id = isset($options->call_id) ? $options->call_id : $this->id;
    $this->nodeId = isset($options->node_id) ? $options->node_id : $this->nodeId;
    $this->context = isset($options->context) ? $options->context : $this->context;
    $this->tag = Uuid::uuid4()->toString();

    $this->relayInstance->addCall($this);
  }

  public function on(String $event, Callable $fn) {
    $this->_cbQueue[$event] = $fn;
    return $this;
  }

  public function off(String $event, Callable $fn = null) {
    unset($this->_cbQueue[$event]);
    return $this;
  }

  public function begin() {
    $msg = new Execute(array(
      'protocol' => $this->relayInstance->protocol,
      'method' => 'call.begin',
      'params' => array(
        'tag' => $this->tag,
        'device' => json_decode(json_encode($this->device), true)
      )
    ));

    return $this->_execute($msg);
  }

  public function hangup() {
    $msg = new Execute(array(
      'protocol' => $this->relayInstance->protocol,
      'method' => 'call.end',
      'params' => array(
        'node_id' => $this->nodeId,
        'call_id' => $this->id,
        'reason' => 'hangup'
      )
    ));

    $blocker = new Blocker(Notification::State, function($params) use (&$blocker) {
      if ($params->call_state === 'ended') {
        ($blocker->resolve)($this);
      }
    });
    $blocker->controlId = $this->id;

    array_push($this->_blockers, $blocker);

    return $this->_execute($msg)->then(function($result) use (&$blocker) {
      return $blocker->promise;
    });
  }

  public function answer() {
    $msg = new Execute(array(
      'protocol' => $this->relayInstance->protocol,
      'method' => 'call.answer',
      'params' => array(
        'node_id' => $this->nodeId,
        'call_id' => $this->id
      )
    ));

    $blocker = new Blocker(Notification::State, function($params) use (&$blocker) {
      if ($params->call_state === 'answered') {
        ($blocker->resolve)($this);
      }
    });
    $blocker->controlId = $this->id;

    array_push($this->_blockers, $blocker);

    return $this->_execute($msg)->then(function($result) use (&$blocker) {
      return $blocker->promise;
    });
  }

  public function playAudioAsync(String $url) {
    $params = ['type' => 'audio', 'params' => ['url' => $url]];
    return $this->_playAsync([$params])->then(function($result) {
      return new PlayAction($this, $result->control_id);
    });
  }

  public function playAudio(String $url) {
    $params = ['type' => PlayType::Audio, 'params' => ['url' => $url]];
    return $this->_play([$params]);
  }

  public function playSilenceAsync(String $duration) {
    $params = ['type' => 'silence', 'params' => ['duration' => $duration]];
    return $this->_playAsync([$params])->then(function($result) {
      return new PlayAction($this, $result->control_id);
    });
  }

  public function playSilence(String $duration) {
    $params = ['type' => PlayType::Silence, 'params' => ['duration' => $duration]];
    return $this->_play([$params]);
  }

  public function playTTSAsync(Array $options) {
    $params = ['type' => 'tts', 'params' => $options];
    return $this->_playAsync([$params])->then(function($result) {
      return new PlayAction($this, $result->control_id);
    });
  }

  public function playTTS(Array $options) {
    $params = ['type' => PlayType::TTS, 'params' => $options];
    return $this->_play([$params]);
  }

  public function playMediaAsync(...$play) {
    return $this->_playAsync($play)->then(function($result) {
      return new PlayAction($this, $result->control_id);
    });
  }

  public function playMedia(...$play) {
    return $this->_play($play);
  }

  public function recordAsync(Array $record) {
    $action = new RecordAction($this);

    $this->_actions[$action->controlId] = $action;
    return $this->_record($record, $action->controlId)->then(function($result) use (&$action) {
      return $action;
    });
  }

  public function record(Array $record) {
    $blocker = new Blocker(Notification::Record, function($params) use (&$blocker) {
      if ($params->state === 'finished' || $params->state === 'no_input') {
        ($blocker->resolve)($params);
      }
    });

    array_push($this->_blockers, $blocker);
    return $this->_record($record, $blocker->controlId)->then(function($result) use (&$blocker) {
      return $blocker->promise;
    });
  }

  public function playAudioAndCollectAsync(Array $collect, String $url) {
    $params = ['type' => PlayType::Audio, 'params' => ['url' => $url]];
    return $this->_playAndCollectAsync($collect, [$params])->then(function($result) {
      return new PromptAction($this, $result->control_id);
    });
  }

  public function playAudioAndCollect(Array $collect, String $url) {
    $params = ['type' => PlayType::Audio, 'params' => ['url' => $url]];
    return $this->_playAndCollect($collect, [$params]);
  }

  public function playSilenceAndCollectAsync(Array $collect, String $duration) {
    $params = ['type' => PlayType::Silence, 'params' => ['duration' => $duration]];
    return $this->_playAndCollectAsync($collect, [$params])->then(function($result) {
      return new PromptAction($this, $result->control_id);
    });
  }

  public function playSilenceAndCollect(Array $collect, String $duration) {
    $params = ['type' => PlayType::Silence, 'params' => ['duration' => $duration]];
    return $this->_playAndCollect($collect, [$params]);
  }

  public function playTTSAndCollectAsync(Array $collect, Array $options) {
    $params = ['type' => PlayType::TTS, 'params' => $options];
    return $this->_playAndCollectAsync($collect, [$params])->then(function($result) {
      return new PromptAction($this, $result->control_id);
    });
  }

  public function playTTSAndCollect(Array $collect, Array $options) {
    $params = ['type' => PlayType::TTS, 'params' => $options];
    return $this->_playAndCollect($collect, [$params]);
  }

  public function playMediaAndCollectAsync(Array $collect, ...$play) {
    return $this->_playAndCollectAsync($collect, $play)->then(function($result) {
      return new PromptAction($this, $result->control_id);
    });
  }

  public function playMediaAndCollect(Array $collect, ...$play) {
    return $this->_playAndCollect($collect, $play);
  }

  public function connectAsync(...$devices) {
    $msg = new Execute(array(
      'protocol' => $this->relayInstance->protocol,
      'method' => 'call.connect',
      'params' => array(
        'node_id' => $this->nodeId,
        'call_id' => $this->id,
        'devices' => \SignalWire\reduceConnectParams($devices, $this->from, $this->timeout)
      )
    ));

    return $this->_execute($msg);
  }

  public function connect(...$devices) {
    $blocker = new Blocker(Notification::Connect, function($params) use (&$blocker) {
      if ($params->connect_state === 'connected') {
        ($blocker->resolve)($this);
      } elseif ($params->connect_state === 'failed') {
        ($blocker->reject)($params);
      }
    });
    $blocker->controlId = $this->id;

    array_push($this->_blockers, $blocker);

    return $this->connectAsync(...$devices)->then(function($result) use (&$blocker) {
      return $blocker->promise;
    });
  }

  public function _stateChange($params) {
    $this->prevState = $this->state;
    $this->state = $params->call_state;
    $this->_dispatchCallback('stateChange');
    $this->_dispatchCallback($params->call_state);
    $this->_addControlParams($params);
    if ($params->call_state === CallState::Ended) {
      $this->relayInstance->removeCall($this);
    }
  }

  public function _connectStateChange($params) {
    $this->prevConnectState = $this->connectState;
    $this->connectState = $params->connect_state;
    $this->_addControlParams($params);
    $this->_dispatchCallback("connect.stateChange");
    $this->_dispatchCallback("connect.$params->connect_state");
  }

  public function _recordStateChange($params) {
    $this->_addControlParams($params);
    $this->_checkAction($params);
    $this->_dispatchCallback('record.stateChange', $params);
    $this->_dispatchCallback("record.$params->state", $params);
  }

  public function _playStateChange($params) {
    $this->_addControlParams($params);
    $this->_dispatchCallback('play.stateChange', $params);
    $this->_dispatchCallback("play.$params->state", $params);
  }

  public function _collectStateChange($params) {
    $this->_addControlParams($params);
    $this->_dispatchCallback('collect', $params);
  }

  private function _dispatchCallback(string $key, ...$params) {
    if (isset($this->_cbQueue[$key]) && is_callable($this->_cbQueue[$key])) {
      call_user_func($this->_cbQueue[$key], $this, ...$params);
    }
  }

  public function _execute(Execute $msg) {
    return $this->relayInstance->client->execute($msg)->then(function($result) {
      return $result->result;
    }, function($error) {
      $e = isset($error->result) ? $error->result : $error;
      throw new \Exception($e->message, $e->code);
    });
  }

  private function _addControlParams($params) {
    if (!isset($params->event_type)) {
      return;
    }
    $controlId = isset($params->control_id) ? $params->control_id : $params->call_id;
    foreach ($this->_blockers as $b) {
      if ($controlId === $b->controlId && $params->event_type === $b->eventType) {
        ($b->resolver)($params);
      }
    }
  }

  private function _checkAction($params) {
    // If exists an Action for this controlId ...
    $controlId = $params->control_id;
    if ($controlId && isset($this->_actions[$controlId])) {
      $this->_actions[$controlId]->update($params);
    }
  }

  private function _play(Array $play) {
    $blocker = new Blocker(Notification::Play, function($params) use (&$blocker) {
      if ($params->state === 'finished') {
        ($blocker->resolve)($this);
      } elseif ($params->state === 'error') {
        ($blocker->reject)();
      }
    });

    array_push($this->_blockers, $blocker);
    return $this->_playAsync($play, $blocker->controlId)->then(function($result) use (&$blocker) {
      return $blocker->promise;
    });
  }

  private function _playAsync(Array $play, String $controlId = null) {
    if (is_null($controlId)) {
      $controlId = Uuid::uuid4()->toString();
    }
    $msg = new Execute(array(
      'protocol' => $this->relayInstance->protocol,
      'method' => 'call.play',
      'params' => array(
        'node_id' => $this->nodeId,
        'call_id' => $this->id,
        'control_id' => $controlId,
        'play' => $play
      )
    ));

    return $this->_execute($msg);
  }

  private function _playAndCollect(Array $collect, Array $play) {
    $blocker = new Blocker(Notification::Collect, function($params) use (&$blocker) {
      $method = $params->result->type === 'error' ? 'reject' : 'resolve';
      ($blocker->$method)($params->result);
    });

    array_push($this->_blockers, $blocker);

    return $this->_playAndCollectAsync($collect, $play, $blocker->controlId)->then(function($result) use (&$blocker) {
      return $blocker->promise;
    });
  }

  private function _playAndCollectAsync(Array $collect, Array $play, String $controlId = null) {
    if (is_null($controlId)) {
      $controlId = Uuid::uuid4()->toString();
    }
    $msg = new Execute(array(
      'protocol' => $this->relayInstance->protocol,
      'method' => 'call.play_and_collect',
      'params' => array(
        'node_id' => $this->nodeId,
        'call_id' => $this->id,
        'control_id' => $controlId,
        'collect' => $collect,
        'play' => $play
      )
    ));

    return $this->_execute($msg);
  }

  private function _record(Array $record, String $controlId = null) {
    if (is_null($controlId)) {
      $controlId = Uuid::uuid4()->toString();
    }
    $msg = new Execute(array(
      'protocol' => $this->relayInstance->protocol,
      'method' => 'call.record',
      'params' => array(
        'node_id' => $this->nodeId,
        'call_id' => $this->id,
        'control_id' => $controlId,
        'record' => $record
      )
    ));

    return $this->_execute($msg);

  }

  /**
   * Dynamic getter for Call state.
   *
   * @param string $name Property name to return
   */
  public function __get($name) {
    switch ($name) {
      case in_array($name, CallState::STATES):
        return $this->state === $name;
      case 'active':
        return $this->state === CallState::Answered;
      // case 'failed':
      //   return false;
      // case 'busy':
      //   return false;
      default:
        return null;
    }
  }
}
