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

    switch ($notification->event) {
      case 'queuing.relay.tasks':
        $client->getTasking()->notificationHandler($notification->params);
        break;
      case 'queuing.relay.messaging':
        $client->getMessaging()->notificationHandler($notification->params);
        break;
      case 'queuing.relay.events':
        self::switchEventType($client, $notification->params->event_type, $notification->params);
        break;
      default:
        Log::warning("Unknown notification event: {$notification->event}");
        break;
    }
  }

  static function switchEventType(Client $client, string $eventType, $params) {
    switch ($eventType) {
      case CallNotification::State:
      case CallNotification::Receive:
      case CallNotification::Connect:
      case CallNotification::Record:
      case CallNotification::Play:
      case CallNotification::Collect:
      case CallNotification::Fax:
      case CallNotification::Detect:
      case CallNotification::Tap:
      case CallNotification::SendDigits:
        $client->getCalling()->notificationHandler($params);
        break;
      default:
        Log::warning("Unknown notification type: {$eventType}");
        break;
    }
  }

}
