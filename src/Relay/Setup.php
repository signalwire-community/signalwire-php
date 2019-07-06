<?php

namespace SignalWire\Relay;

use SignalWire\Messages\Execute;
use SignalWire\Log;

class Setup {

  const Protocol = 'signalwire';
  const Method = 'setup';
  const Channels = ['notifications'];

  static function protocol(Client $client) {
    $msg = new Execute(array(
      'protocol' => self::Protocol,
      'method' => self::Method,
      'params' => array('service' => '')
    ));
    return $client->execute($msg)->then(function ($response) use ($client) {
      return $client->subscribe($response->result->protocol, self::Channels)->then(function ($response) {
        return $response->protocol;
      }, function ($error) use ($client) {
        Log::error("Setup error: {$error->message}. [code: {$error->code}]");
        $client->eventLoop->stop();
      });
    }, function($error) use ($client) {
      Log::error("Setup error: {$error->message}. [code: {$error->code}]");
      $client->eventLoop->stop();
    });
  }

}
