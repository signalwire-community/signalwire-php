<?php
namespace SignalWire\Relay\Calling;
use SignalWire\Messages\Execute;
use SignalWire\Handler;
use SignalWire\Log;

class Calling extends \SignalWire\Relay\BaseRelay {
  const Service = 'calling';

  private $_calls = array();

  public function getServiceName(): String {
    return self::Service;
  }

  public function notificationHandler($notification): void {
    $notification->params->event_type = $notification->event_type;
    switch ($notification->event_type)
    {
      case Notification::State:
        $this->_onState($notification->params);
        break;
      case Notification::Connect:
        $this->_onConnect($notification->params);
        break;
      case Notification::Record:
        $this->_onRecord($notification->params);
        break;
      case Notification::Play:
        $this->_onPlay($notification->params);
        break;
      case Notification::Collect:
        $this->_onCollect($notification->params);
        break;
      case Notification::Receive:
        $call = new Call($this, $notification->params);
        Handler::trigger($this->protocol, $call, $this->_prefixCtx($call->context));
        break;
    }
  }

  public function newCall(Array $params) {
    $options = (object)[
      'device' => (object)[
        'type' => $params['type'],
        'params' => (object)[
          'from_number' => isset($params['from']) ? $params['from'] : null,
          'to_number' => isset($params['to']) ? $params['to'] : null,
          'timeout' => isset($params['timeout']) ? $params['timeout'] : 30
        ]
      ]
    ];
    return $this->ready->then(function($protocol) use ($options) {
      return new Call($this, $options);
    })->otherwise([$this, '_onError']);
  }

  public function onInbound(String $context, Callable $handler) {
    if (!$context || !is_callable($handler)) {
      throw new Exception("Invalid parameters");
    }
    return $this->ready->then(function($protocol) use ($context, $handler) {
      $msg = new Execute([
        'protocol' => $protocol,
        'method' => 'call.receive',
        'params' => [ 'context' => $context ]
      ]);
      return $this->client->execute($msg)->then(function($response) use ($protocol, $context, $handler) {
        Handler::register($protocol, $handler, $this->_prefixCtx($context));
        return $response->result;
      }, [$this, '_onError']);
    }, [$this, '_onError']);
  }

  public function _onError($error) {
    throw new \Exception($error->message, $error->code);
  }

  private function _prefixCtx(String $context) {
    return "ctx:$context";
  }

  public function addCall(Call $call) {
    array_push($this->_calls, $call);
  }

  public function removeCall(Call $call) {
    foreach ($this->_calls as $index => $c) {
      if ($c->id === $call->id) {
        array_splice($this->_calls, $index, 1);
        return;
      }
    }
  }

  private function getCallById(String $callId) {
    foreach ($this->_calls as $call) {
      if ($call->id === $callId) {
        return $call;
      }
    }
    return false;
  }

  private function getCallByTag(String $tag) {
    foreach ($this->_calls as $call) {
      if ($call->tag === $tag) {
        return $call;
      }
    }
    return false;
  }

  private function _onState($params) {
    $call = $this->getCallById($params->call_id);
    if (!$call && isset($params->tag)) {
      $call = $this->getCallByTag($params->tag);
    }
    if ($call) {
      if (!$call->id && isset($params->call_id) && isset($params->node_id)) {
        $call->id = $params->call_id;
        $call->nodeId = $params->node_id;
      }
      $call->_stateChange($params);
    } elseif (isset($params->call_id) && isset($params->peer)) {
      $call = new Call($this, $params);
    } else {
      Log::error('Unknown call', $params);
    }
  }

  private function _onRecord($params) {
    $call = $this->getCallById($params->call_id);
    if ($call) {
      $call->_recordStateChange($params);
    }
  }

  private function _onPlay($params) {
    $call = $this->getCallById($params->call_id);
    if ($call) {
      $call->_playStateChange($params);
    }
  }

  private function _onCollect($params) {
    $call = $this->getCallById($params->call_id);
    if ($call) {
      $call->_collectStateChange($params);
    }
  }

  private function _onConnect($params) {
    $call = $this->getCallById($params->call_id);
    if ($call) {
      if (isset($params->peer) && isset($params->peer->call_id)) {
        $call->peer = $this->getCallById($params->peer->call_id);
      }
      $call->_connectStateChange($params);
    }
  }
}
