<?php

namespace SignalWire\Relay\Calling;

use SignalWire\Handler;
use SignalWire\Log;

class Calling extends \SignalWire\Relay\BaseRelay {

  private $_calls = array();

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
      case Notification::Fax:
        $this->_onFax($notification->params);
        break;
      case Notification::Detect:
        $this->_onDetect($notification->params);
        break;
      case Notification::Tap:
        $this->_onTap($notification->params);
        break;
      case Notification::SendDigits:
        $this->_onSendDigits($notification->params);
        break;
      case Notification::Receive:
        $call = new Call($this, $notification->params);
        Handler::trigger($this->client->relayProtocol, $call, $this->_ctxReceiveUniqueId($call->context));
        break;
    }
  }

  public function newCall(Array $params) {
    return new Call($this, $this->_buildDevice($params));
  }

  public function dial(Array $params) {
    $call = new Call($this, $this->_buildDevice($params));
    return $call->dial();
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

  public function getCallById(String $callId) {
    foreach ($this->_calls as $call) {
      if ($call->id === $callId) {
        return $call;
      }
    }
    return false;
  }

  public function getCallByTag(String $tag) {
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
      Log::error('Unknown call', (array)$params);
    }
  }

  private function _onRecord($params) {
    $call = $this->getCallById($params->call_id);
    if ($call) {
      $call->_recordChange($params);
    }
  }

  private function _onPlay($params) {
    $call = $this->getCallById($params->call_id);
    if ($call) {
      $call->_playChange($params);
    }
  }

  private function _onCollect($params) {
    $call = $this->getCallById($params->call_id);
    if ($call) {
      $call->_collectChange($params);
    }
  }

  private function _onFax($params) {
    $call = $this->getCallById($params->call_id);
    if ($call) {
      $call->_faxChange($params);
    }
  }

  private function _onDetect($params) {
    $call = $this->getCallById($params->call_id);
    if ($call) {
      $call->_detectChange($params);
    }
  }

  private function _onTap($params) {
    $call = $this->getCallById($params->call_id);
    if ($call) {
      $call->_tapChange($params);
    }
  }

  private function _onConnect($params) {
    $call = $this->getCallById($params->call_id);
    if ($call) {
      $call->_connectChange($params);
    }
  }

  private function _onSendDigits($params) {
    $call = $this->getCallById($params->call_id);
    if ($call) {
      $call->_sendDigitsChange($params);
    }
  }

  private function _buildDevice(Array $params) {
    return (object)[
      'device' => (object)[
        'type' => $params['type'],
        'params' => (object)[
          'from_number' => isset($params['from']) ? $params['from'] : null,
          'to_number' => isset($params['to']) ? $params['to'] : null,
          'timeout' => isset($params['timeout']) ? $params['timeout'] : 30
        ]
      ]
    ];
  }
}
