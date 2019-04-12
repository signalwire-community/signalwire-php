<?php
namespace SignalWire\Relay\Calling;
// use SignalWire\Relay;

class Calling extends \SignalWire\Relay\BaseRelay {
  const Service = 'calling';

  public function getServiceName(): String {
    return self::Service;
  }

  public function newCall() {
    echo PHP_EOL . "NewCall start...";
    $this->ready->then(
      function($protocol) {
        echo PHP_EOL . "NewCall Ready to rock!";
      },
      function($error) {
        echo PHP_EOL . "NewCall Error!";
        print_r($error);
      }
    );
  }
}
