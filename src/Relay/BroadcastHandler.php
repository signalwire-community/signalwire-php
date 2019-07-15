<?php

namespace SignalWire\Relay;

use SignalWire\Relay\Calling\Notification as CallNotification;
use SignalWire\Log;

class BroadcastHandler {

  static function notification(Client $client, $notification) {
    if ($client->relayProtocol !== $notification->protocol) {
      Log::debug('Broadcast protocol mismatch.');
      return;
    }

    switch ($notification->params->event_type) {
      case CallNotification::State:
      case CallNotification::Receive:
      case CallNotification::Connect:
      case CallNotification::Record:
      case CallNotification::Play:
      case CallNotification::Collect:
      case CallNotification::Fax:
        $client->getCalling()->notificationHandler($notification->params);
        break;
      default:
        Log::warning("Unknown notification type: {$notification->params->event_type}");
        break;
    }
  }

}
