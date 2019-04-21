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
    switch ($notification->event_type)
    {
      case 'calling.call.state':
        $this->_onState($notification->params);
        break;
      case 'calling.call.connect':
        break;
      case 'calling.call.record':
        break;
      case 'calling.call.play':
        break;
      case 'calling.call.collect':
        break;
      case 'calling.call.receive':
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
    return $this->ready->then(
      function($protocol) use ($options) {
        return new Call($this, $options);
      },
      function($error) {
        echo PHP_EOL . "NewCall Error!";
        print_r($error);
      }
    );
  }

  public function onInbound(String $context, Callable $handler) {
    if (!$context || !is_callable($handler)) {
      throw new Exception("Invalid parameters");
    }
    $this->ready->then(
      function($protocol) use ($context, $handler) {
        $msg = new Execute([
          'protocol' => $protocol,
          'method' => 'call.receive',
          'params' => [ 'context' => $context ]
        ]);
        $this->client->execute($msg)->then(
          function($response) use ($protocol, $context, $handler) {
            Handler::register($protocol, $handler, $this->_prefixCtx($context));
          }, function($error) {
            // TODO: throw exception
            Log::warning('onInbound error:');
            print_r($error);
          }
        );
      },
      function($error) {
        echo PHP_EOL . "NewCall Error!";
        print_r($error);
      }
    );
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
    if ($call) {
      return Handler::trigger($params->call_id, $call, $params->call_state);
    }
    $call = $this->getCallByTag($params->tag);
    if ($call) {
      if (!$call->id) {
        $call->setup($params->call_id, $params->node_id);
      }
      return Handler::trigger($params->call_id, $call, $params->call_state);
    }
    if ($params->call_id && $params->peer) {
      $call = new Call($this, $params);
      return;
    }
    Log::error('Unknown call', (array)$params);
  }
}
