<?php

namespace SignalWire\Relay\Messaging;

use SignalWire\Messages\Execute;
use SignalWire\Handler;
use SignalWire\Log;

class Messaging extends \SignalWire\Relay\BaseRelay {

  public function notificationHandler($notification): void {
    $notification->params->event_type = $notification->event_type;
    $message = new Message($notification->params);
    switch ($notification->event_type)
    {
      case Notification::State:
        Log::info("Relay message '{$message->id}' changes state to '{$message->state}'");
        Handler::trigger($this->client->relayProtocol, $message, $this->_ctxStateUniqueId($message->context));
        break;
      case Notification::Receive:
        Log::info("New Relay {$message->direction} message in context '{$message->context}'");
        Handler::trigger($this->client->relayProtocol, $message, $this->_ctxReceiveUniqueId($message->context));
        break;
    }
  }

  public function send(Array $params) {
    $params['from_number'] = isset($params['from']) ? $params['from'] : '';
    $params['to_number'] = isset($params['to']) ? $params['to'] : '';
    unset($params['from'], $params['to']);
    $msg = new Execute([
      'protocol' => $this->client->relayProtocol,
      'method' => 'messaging.send',
      'params' => $params
    ]);
    return $this->client->execute($msg)->then(function($response) {
      Log::info($response->result->message);
      return new SendResult($response->result);
    }, function ($error) {
      Log::error("Messaging send error: {$error->message}. [code: {$error->code}]");
      return new SendResult($error);
    });
  }
}
