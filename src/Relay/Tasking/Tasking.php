<?php
namespace SignalWire\Relay\Tasking;

use SignalWire\Handler;
use SignalWire\Log;

class Tasking extends \SignalWire\Relay\BaseRelay {

  public function notificationHandler($notification): void {
    Log::info("Receive task in context: {$notification->context}");
    Handler::trigger($this->client->relayProtocol, $notification->message, $this->_ctxReceiveUniqueId($notification->context));
  }

}
