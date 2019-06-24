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

    $hangupResult = new HangupResult();
    $blocker = new Blocker(Notification::State, function($params) use (&$blocker, &$hangupResult) {
      if ($params->call_state === CallState::Ended) {
        $hangupResult->reason = isset($params->reason) ? $params->reason : 'hangup';
        ($blocker->resolve)($hangupResult);
      }
    });
    $blocker->controlId = $this->id;

    array_push($this->_blockers, $blocker);

    return $this->_execute($msg)->then(function($result) use (&$blocker, &$hangupResult) {
      $hangupResult->result = $result;
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

    $answerResult = new AnswerResult();
    $blocker = new Blocker(Notification::State, function($params) use (&$blocker, &$answerResult) {
      if ($params->call_state === 'answered') {
        ($blocker->resolve)($answerResult);
      }
    });
    $blocker->controlId = $this->id;

    array_push($this->_blockers, $blocker);

    return $this->_execute($msg)->then(function($result) use (&$blocker, &$answerResult) {
      $answerResult->result = $result;
      return $blocker->promise;
    });
  }

  public function playAudioAsync(String $url) {
    $action = $this->_buildAction('SignalWire\Relay\Calling\PlayAction');

    $params = ['type' => PlayType::Audio, 'params' => ['url' => $url]];
    return $this->_playAsync([$params], $action->controlId)->then(function($result) use (&$action) {
      return $action;
    });
  }

  public function playAudio(String $url) {
    $params = ['type' => PlayType::Audio, 'params' => ['url' => $url]];
    return $this->_play([$params]);
  }

  public function playSilenceAsync(String $duration) {
    $action = $this->_buildAction('SignalWire\Relay\Calling\PlayAction');

    $params = ['type' => PlayType::Silence, 'params' => ['duration' => $duration]];
    return $this->_playAsync([$params], $action->controlId)->then(function($result) use (&$action) {
      return $action;
    });
  }

  public function playSilence(String $duration) {
    $params = ['type' => PlayType::Silence, 'params' => ['duration' => $duration]];
    return $this->_play([$params]);
  }

  public function playTTSAsync(Array $options) {
    $action = $this->_buildAction('SignalWire\Relay\Calling\PlayAction');

    $params = ['type' => PlayType::TTS, 'params' => $options];
    return $this->_playAsync([$params], $action->controlId)->then(function($result) use (&$action) {
      return $action;
    });
  }

  public function playTTS(Array $options) {
    $params = ['type' => PlayType::TTS, 'params' => $options];
    return $this->_play([$params]);
  }

  public function playAsync(...$play) {
    $action = $this->_buildAction('SignalWire\Relay\Calling\PlayAction');

    return $this->_playAsync($play, $action->controlId)->then(function($result) use (&$action) {
      return $action;
    });
  }

  public function play(...$play) {
    return $this->_play($play);
  }

  public function recordAsync(Array $record) {
    $action = $this->_buildAction('SignalWire\Relay\Calling\RecordAction');

    return $this->_record($record, $action->controlId)->then(function($result) use (&$action) {
      return $action;
    });
  }

  public function record(Array $record) {
    $blocker = new Blocker(Notification::Record, function($params) use (&$blocker) {
      if ($params->state !== RecordState::Recording) {
        $result = new RecordResult($params);
        ($blocker->resolve)($result);
      }
    });

    array_push($this->_blockers, $blocker);
    return $this->_record($record, $blocker->controlId)->then(function($result) use (&$blocker) {
      return $blocker->promise;
    });
  }

  public function promptAudioAsync(Array $collect, String $url) {
    $action = $this->_buildAction('SignalWire\Relay\Calling\PromptAction');

    $params = ['type' => PlayType::Audio, 'params' => ['url' => $url]];
    return $this->_promptAsync($collect, [$params], $action->controlId)->then(function($result) use (&$action) {
      return $action;
    });
  }

  public function promptAudio(Array $collect, String $url) {
    $params = ['type' => PlayType::Audio, 'params' => ['url' => $url]];
    return $this->_prompt($collect, [$params]);
  }

  // public function playSilenceAndCollectAsync(Array $collect, String $duration) {
  //   $action = $this->_buildAction('SignalWire\Relay\Calling\PromptAction');

  //   $params = ['type' => PlayType::Silence, 'params' => ['duration' => $duration]];
  //   return $this->_promptAsync($collect, [$params], $action->controlId)->then(function($result) use (&$action) {
  //     return $action;
  //   });
  // }

  // public function playSilenceAndCollect(Array $collect, String $duration) {
  //   $params = ['type' => PlayType::Silence, 'params' => ['duration' => $duration]];
  //   return $this->_prompt($collect, [$params]);
  // }

  public function promptTTSAsync(Array $collect, Array $options) {
    $action = $this->_buildAction('SignalWire\Relay\Calling\PromptAction');

    $params = ['type' => PlayType::TTS, 'params' => $options];
    return $this->_promptAsync($collect, [$params], $action->controlId)->then(function($result) use (&$action) {
      return $action;
    });
  }

  public function promptTTS(Array $collect, Array $options) {
    $params = ['type' => PlayType::TTS, 'params' => $options];
    return $this->_prompt($collect, [$params]);
  }

  public function promptAsync(Array $collect, ...$play) {
    $action = $this->_buildAction('SignalWire\Relay\Calling\PromptAction');

    return $this->_promptAsync($collect, $play, $action->controlId)->then(function($result) use (&$action) {
      return $action;
    });
  }

  public function prompt(Array $collect, ...$play) {
    return $this->_prompt($collect, $play);
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
    $this->_checkBlockers($params);
    if ($params->call_state === CallState::Ended) {
      $this->relayInstance->removeCall($this);
    }
  }

  public function _connectStateChange($params) {
    $this->prevConnectState = $this->connectState;
    $this->connectState = $params->connect_state;
    $this->_checkBlockers($params);
    $this->_dispatchCallback("connect.stateChange");
    $this->_dispatchCallback("connect.$params->connect_state");
  }

  public function _recordStateChange($params) {
    $this->_checkBlockers($params);
    $this->_checkActions($params);
    $this->_dispatchCallback('record.stateChange', $params);
    $this->_dispatchCallback("record.$params->state", $params);
  }

  public function _playStateChange($params) {
    $this->_checkBlockers($params);
    $this->_checkActions($params);
    $this->_dispatchCallback('play.stateChange', $params);
    $this->_dispatchCallback("play.$params->state", $params);
  }

  public function _collectStateChange($params) {
    $this->_checkBlockers($params);
    $this->_checkActions($params);
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

  private function _checkBlockers($params) {
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

  private function _checkActions($params) {
    // If exists an Action for this controlId ...
    $controlId = $params->control_id;
    if ($controlId && isset($this->_actions[$controlId])) {
      $this->_actions[$controlId]->update($params);
    }
  }

  private function _play(Array $play) {
    $blocker = new Blocker(Notification::Play, function($params) use (&$blocker) {
      if ($params->state !== PlayState::Playing) {
        $result = new PlayResult($params);
        ($blocker->resolve)($result);
      }
    });

    array_push($this->_blockers, $blocker);
    return $this->_playAsync($play, $blocker->controlId)->then(function($result) use (&$blocker) {
      return $blocker->promise;
    });
  }

  private function _playAsync(Array $play, String $controlId) {
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

  private function _prompt(Array $collect, Array $play) {
    $blocker = new Blocker(Notification::Collect, function($params) use (&$blocker) {
      $result = new PromptResult($params);
      ($blocker->resolve)($result);
    });

    array_push($this->_blockers, $blocker);

    return $this->_promptAsync($collect, $play, $blocker->controlId)->then(function($result) use (&$blocker) {
      return $blocker->promise;
    });
  }

  private function _promptAsync(Array $collect, Array $play, String $controlId) {
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

  private function _record(Array $record, String $controlId) {
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
   * Build an *Action object, cache it in the hashmap and return
   *
   * @param string $name Class name with namespace to build
   * @return SignalWire\Relay\Calling\*Action Action object built
   */
  private function _buildAction(String $className) {
    $action = new $className($this);
    $this->_actions[$action->controlId] = $action;

    return $action;
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
