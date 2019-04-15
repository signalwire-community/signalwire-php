<?php
namespace SignalWire\Relay\Calling;

class Calling extends \SignalWire\Relay\BaseRelay {
  const Service = 'calling';

  public function getServiceName(): String {
    return self::Service;
  }

  public function notificationHandler($notification): void {
    echo PHP_EOL . "Calling notificationHandler" . PHP_EOL;
    print_r($notification);
  }

  public function newCall(Array $params) {
    echo PHP_EOL . "NewCall start...";
    $this->ready->then(
      function($protocol) {
        echo PHP_EOL . "NewCall Ready to rock on proto: " . $protocol;
      },
      function($error) {
        echo PHP_EOL . "NewCall Error!";
        print_r($error);
      }
    );
  }
}
