<?php
namespace SignalWire\Relay\Calling;
use SignalWire\Messages\Execute;
use SignalWire\Handler;

class Calling extends \SignalWire\Relay\BaseRelay {
  const Service = 'calling';

  public function getServiceName(): String {
    return self::Service;
  }

  public function notificationHandler($notification): void {
    echo PHP_EOL . "Calling notificationHandler" . PHP_EOL;
    print_r($notification);
    switch ($notification->event_type)
    {
      case 'calling.call.state':
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
      'device' => [
        'type' => $params['type'],
        'params' => [
          'from_number' => $params['from'],
          'to_number' => $params['to'],
          'timeout' => $params['timeout']
        ]
      ]
    ];

    echo PHP_EOL . "NewCall start...";
    $this->ready->then(
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
}
