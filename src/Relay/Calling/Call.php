<?php
namespace SignalWire\Relay\Calling;
use SignalWire\Messages\Execute;
use Ramsey\Uuid\Uuid;
use SignalWire\Relay\Calling\Notification;
use SignalWire\Relay\Calling\RecordAction;
use SignalWire\Relay\Calling\PlayMediaAction;
use SignalWire\Relay\Calling\PlayAudioAction;
use SignalWire\Relay\Calling\PlaySilenceAction;
use SignalWire\Relay\Calling\PlayTTSAction;
use SignalWire\Relay\Calling\PlayAudioAndCollectAction;
use SignalWire\Relay\Calling\PlaySilenceAndCollectAction;
use SignalWire\Relay\Calling\PlayTTSAndCollectAction;
use SignalWire\Relay\Calling\PlayMediaAndCollectAction;

class Call {
  const DefaultTimeout = 30;
  const STATES = ['none', 'created', 'ringing', 'answered', 'ending', 'ended'];
  public $id = false;
  public $nodeId = false;
  public $relayInstance;
  public $ready = false;
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

    return $this->_execute($msg);
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

    return $this->_execute($msg);
  }

  public function playAudio(String $url) {
    $params = ['type' => 'audio', 'params' => ['url' => $url]];
    return $this->_play([$params]);
  }

  public function playSilence(String $duration) {
    $params = ['type' => 'silence', 'params' => ['duration' => $duration]];
    return $this->_play([$params]);
  }

  public function playTTS(Array $options) {
    $params = ['type' => 'tts', 'params' => $options];
    return $this->_play([$params]);
  }

  public function playMedia(...$play) {
    return $this->_play($play);
  }

  public function record(Array $record) {
    $msg = new Execute(array(
      'protocol' => $this->relayInstance->protocol,
      'method' => 'call.record',
      'params' => array(
        'node_id' => $this->nodeId,
        'call_id' => $this->id,
        'control_id' => Uuid::uuid4()->toString(),
        'record' => $record
      )
    ));

    return $this->_execute($msg)->then(function($result) {
      return new RecordAction($this, $result->control_id);
    });
  }

  public function playAudioAndCollect(Array $collect, String $url) {
    $params = ['type' => 'audio', 'params' => ['url' => $url]];
    return $this->_playAndCollect($collect, [$params])->then(function($result) {
      return new PlayAudioAndCollectAction($this, $result->control_id);
    });
  }

  public function playSilenceAndCollect(Array $collect, String $duration) {
    $params = ['type' => 'silence', 'params' => ['duration' => $duration]];
    return $this->_playAndCollect($collect, [$params])->then(function($result) {
      return new PlaySilenceAndCollectAction($this, $result->control_id);
    });
  }

  public function playTTSAndCollect(Array $collect, Array $options) {
    $params = ['type' => 'tts', 'params' => $options];
    return $this->_playAndCollect($collect, [$params])->then(function($result) {
      return new PlayTTSAndCollectAction($this, $result->control_id);
    });
  }

  public function playMediaAndCollect(Array $collect, ...$play) {
    return $this->_playAndCollect($collect, $play)->then(function($result) {
      return new PlayMediaAndCollectAction($this, $result->control_id);
    });
  }

  public function connect(...$devices) {
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

  public function _stateChange(String $state) {
    $this->prevState = $this->state;
    $this->state = $state;
    $this->_dispatchCallback('stateChange');
    $this->_dispatchCallback($state);
    $last = count(self::STATES) - 1;
    if ($state === self::STATES[$last]) {
      $this->relayInstance->removeCall($this);
    }
  }

  public function _connectStateChange(String $state) {
    $this->prevConnectState = $this->connectState;
    $this->connectState = $state;
    $this->_dispatchCallback("connect.stateChange");
    $this->_dispatchCallback("connect.$state");
  }

  public function _recordStateChange($params) {
    $this->_addControlParams($params);
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
    })->otherwise(function($error) {
      $e = isset($error->result) ? $error->result : $error;
      throw new \Exception($e->message, $e->code);
    });
  }

  private function _addControlParams($params) {
    if (!isset($params->control_id) || !isset($params->event_type)) {
      return;
    }
    $blocker = null;
    foreach ($this->_blockers as $b) {
      if ($params->control_id === $b->controlId && $params->event_type === $b->eventType) {
        $blocker = $b;
        break;
      }
    }
    if ($blocker) {
      ($blocker->resolver)($params);
    }
  }

  private function _play(Array $play) {
    return $this->_playAsync($play)->then(function($result) {
      $blocker = new Blocker($result->control_id, Notification::Play, function($params) use (&$blocker) {
        if ($params->state === 'finished') {
          ($blocker->resolve)($this);
        } elseif ($params->state === 'error') {
          ($blocker->reject)();
        }
      });

      array_push($this->_blockers, $blocker);

      return $blocker->promise;
    });
  }

  private function _playAsync(Array $play) {
    $msg = new Execute(array(
      'protocol' => $this->relayInstance->protocol,
      'method' => 'call.play',
      'params' => array(
        'node_id' => $this->nodeId,
        'call_id' => $this->id,
        'control_id' => Uuid::uuid4()->toString(),
        'play' => $play
      )
    ));

    return $this->_execute($msg);
  }

  private function _playAndCollect(Array $collect, Array $play) {
    $msg = new Execute(array(
      'protocol' => $this->relayInstance->protocol,
      'method' => 'call.play_and_collect',
      'params' => array(
        'node_id' => $this->nodeId,
        'call_id' => $this->id,
        'control_id' => Uuid::uuid4()->toString(),
        'collect' => $collect,
        'play' => $play
      )
    ));

    return $this->_execute($msg);
  }
}
