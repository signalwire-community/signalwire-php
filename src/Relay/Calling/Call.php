<?php
namespace SignalWire\Relay\Calling;
use SignalWire\Messages\Execute;
use SignalWire\Handler;

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
  public $context;
  public $peer;
  public $device = array();
  public $type = '';
  public $from = '';
  public $to = '';
  public $timeout = self::DefaultTimeout;

  private $_cbQueue = array();

  public function __construct(Calling $relayInstance, $options) {
    $this->relayInstance = $relayInstance;
    $this->device = $options->device;
    $this->type = $this->device->type;

    $this->from = isset($this->device->params->from_number) ? $this->device->params->from_number : $this->from;
    $this->to = isset($this->device->params->to_number) ? $this->device->params->to_number : $this->to;
    $this->timeout = isset($this->device->params->timeout) ? $this->device->params->timeout : $this->timeout;

    $this->tag = \SignalWire\Util\UUID::v4();
    if (isset($options->call_id) && isset($options->node_id)) {
      $this->setup($options->call_id, $options->node_id);
    }
    if (isset($options->context)) {
      $this->context = $options->context;
    }

    $this->relayInstance->addCall($this);
  }

  public function setup(String $callId, String $nodeId) {
    $this->id = $callId;
    $this->nodeId = $nodeId;
    $this->_attachListeners();
  }

  public function on(String $event, Callable $fn) {
    $this->_cbQueue[$event] = $fn;
    return $this;
  }

  public function off(String $event, Callable $fn = null) {
    if ($this->id) {
      Handler::deRegister($this->id, $fn, $event);
    }
    unset($this->_cbQueue[$event]);
    return $this;
  }

  public function begin() {
    $msg = new Execute(array(
      'protocol' => $this->relayInstance->protocol,
      'method' => 'call.begin',
      'params' => array(
        'tag' => $this->tag,
        'device' => $this->device
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
    return $this->playMedia($params);
  }

  public function playSilence(String $duration) {
    $params = ['type' => 'silence', 'params' => ['duration' => $duration]];
    return $this->playMedia($params);
  }

  public function playTTS(Array $options) {
    $params = ['type' => 'tts', 'params' => $options];
    return $this->playMedia($params);
  }

  public function playMedia(...$play) {
    $msg = new Execute(array(
      'protocol' => $this->relayInstance->protocol,
      'method' => 'call.play',
      'params' => array(
        'node_id' => $this->nodeId,
        'call_id' => $this->id,
        'control_id' => \SignalWire\Util\UUID::v4(),
        'play' => $play
      )
    ));

    return $this->_execute($msg);
  }

  public function stopMedia(String $control_id) {
    $msg = new Execute(array(
      'protocol' => $this->relayInstance->protocol,
      'method' => 'call.play.stop',
      'params' => array(
        'node_id' => $this->nodeId,
        'call_id' => $this->id,
        'control_id' => $control_id
      )
    ));

    return $this->_execute($msg);
  }

  public function startRecord(Array $options) {
    $msg = new Execute(array(
      'protocol' => $this->relayInstance->protocol,
      'method' => 'call.record',
      'params' => array(
        'node_id' => $this->nodeId,
        'call_id' => $this->id,
        'control_id' => \SignalWire\Util\UUID::v4(),
        'type' => 'audio',
        'params' => $options
      )
    ));

    return $this->_execute($msg);
  }

  public function stopRecord(String $control_id) {
    $msg = new Execute(array(
      'protocol' => $this->relayInstance->protocol,
      'method' => 'call.record.stop',
      'params' => array(
        'node_id' => $this->nodeId,
        'call_id' => $this->id,
        'control_id' => $control_id
      )
    ));

    return $this->_execute($msg);
  }

  public function playAudioAndCollect(Array $collect, String $url) {
    $params = ['type' => 'audio', 'params' => ['url' => $url]];
    return $this->playAndCollect($collect, $params);
  }

  public function playSilenceAndCollect(Array $collect, String $duration) {
    $params = ['type' => 'silence', 'params' => ['duration' => $duration]];
    return $this->playAndCollect($collect, $params);
  }

  public function playTTSAndCollect(Array $collect, Array $options) {
    $params = ['type' => 'tts', 'params' => $options];
    return $this->playAndCollect($collect, $params);
  }

  public function playAndCollect(Array $collect, ...$play) {
    $msg = new Execute(array(
      'protocol' => $this->relayInstance->protocol,
      'method' => 'call.play_and_collect',
      'params' => array(
        'node_id' => $this->nodeId,
        'call_id' => $this->id,
        'control_id' => \SignalWire\Util\UUID::v4(),
        'collect' => $collect,
        'play' => $play
      )
    ));

    return $this->_execute($msg);
  }

  public function stopPlayAndCollect(String $control_id) {
    $msg = new Execute(array(
      'protocol' => $this->relayInstance->protocol,
      'method' => 'call.play_and_collect.stop',
      'params' => array(
        'node_id' => $this->nodeId,
        'call_id' => $this->id,
        'control_id' => $control_id
      )
    ));

    return $this->_execute($msg);
  }

  public function _addControlParams($params) {
    if (!isset($params->control_id) || !isset($params->event_type)) {
      return;
    }
    $index = null;
    foreach ($this->_controls as $i => $c) {
      if ($params->control_id === $c->control_id) {
        $index = $i;
        break;
      }
    }
    if ($index !== null) {
      $this->_controls[$index] = $params;
    } else {
      array_push($this->_controls, $params);
    }
  }

  private function _attachListeners() {
    foreach (self::STATES as $index => $state) {
      Handler::registerOnce($this->id, function() use ($index, $state) {
        $this->prevState = $this->state;
        $this->state = $state;
        if (isset($this->_cbQueue[$state])) {
          call_user_func($this->_cbQueue[$state], $this);
        }
        if ($index === (count(self::STATES) - 1)) {
          $this->_detachListeners();
        }
      }, $state);
    }

    $eventNames = array_keys($this->_cbQueue);
    foreach ($eventNames as $event) {
      if (preg_match("/^(?:record\.|play\.|collect$)/", $event) === 1) {
        Handler::registerOnce($this->id, $this->_cbQueue[$event], $event);
      }
    }
  }

  private function _detachListeners() {
    if ($this->id) {
      Handler::deRegisterAll($this->id);
    }
    $this->relayInstance->removeCall($this);
  }

  private function _execute(Execute $msg) {
    return $this->relayInstance->client->execute($msg)->then(
      function($result) {
        return $result->result;
      },
      function($error) {
        return isset($error->result) ? $error->result : $error;
      }
    );
  }
}
