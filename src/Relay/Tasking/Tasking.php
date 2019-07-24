<?php
namespace SignalWire\Relay\Tasking;

use SignalWire\Handler;
use SignalWire\Log;

class Tasking extends \SignalWire\Relay\BaseRelay {

  public function notificationHandler($notification): void {
    Log::info("Receive task in context: {$notification->context}");
    Handler::trigger($this->client->relayProtocol, $notification->message, $this->_prefixCtx($notification->context));
  }

  // public function onTask(string $context, callable $handler) {
  //   Handler::register($this->client->relayProtocol, $handler, $this->_prefixCtx($context));
  // }

  // private function _prefixCtx(string $context) {
  //   return "tasking.context.$context";
  // }
}
