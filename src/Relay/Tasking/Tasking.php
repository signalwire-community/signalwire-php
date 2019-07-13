<?php
namespace SignalWire\Relay\Tasking;

use SignalWire\Handler;
use SignalWire\Log;

class Tasking extends \SignalWire\Relay\BaseRelay {

  public function notificationHandler($notification): void {
    // $notification->params->event_type = $notification->event_type;
    switch ($notification->event_type)
    {
      case Notification::Receive:
        Log::info("Receive task in context: {$notification->context}");
        Handler::trigger($this->client->relayProtocol, $notification->params, $this->_prefixCtx($notification->context));
        break;
    }
  }

  public function onTask(string $context, callable $handler) {
    Handler::register($this->client->relayProtocol, $handler, $this->_prefixCtx($context));
  }

  private function _prefixCtx(string $context) {
    return "tasking.context.$context";
  }
}
