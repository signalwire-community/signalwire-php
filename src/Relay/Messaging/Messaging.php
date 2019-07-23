<?php

namespace SignalWire\Relay\Messaging;

use SignalWire\Messages\Execute;
use SignalWire\Handler;
use SignalWire\Log;

class Messaging extends \SignalWire\Relay\BaseRelay {

  public function notificationHandler($notification): void {
    $notification->params->event_type = $notification->event_type;
    switch ($notification->event_type)
    {
      case Notification::State:
        // $this->_onState($notification->params);
        break;
      case Notification::Receive:
        $message = new Message($this, $notification->params);
        Handler::trigger($this->client->relayProtocol, $message, $this->_prefixCtx($message->context));
        break;
    }
  }

  public function send(Array $params) {
    $msg = new Execute([
      'protocol' => $this->client->relayProtocol,
      'method' => 'messaging.send',
      'params' => $params
    ]);
    return $this->client->execute($msg)->done(function($response) {
      Log::info($response->result->message);
      return $response->result;
    }, function ($error) {
      Log::error("Messaging send error: {$error->message}. [code: {$error->code}]");
      return $error;
    });
  }

  private function _prefixCtx(String $context) {
    return "messaging.context.$context";
  }
}
