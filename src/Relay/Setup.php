<?php

namespace SignalWire\Relay;

use SignalWire\Messages\Execute;
use SignalWire\Log;

class Setup {

  const Protocol = 'signalwire';
  const Method = 'setup';
  const Channels = ['notifications'];
  const Receive = 'signalwire.receive';

  static function protocol(Client $client) {
    $params = new \stdClass;
    if ($client->relayProtocol) {
      $split = explode('_', $client->relayProtocol);
      if (isset($split[1]) && $split[1] === $client->signature) {
        $params->protocol = $client->relayProtocol;
      }
    }
    $msg = new Execute(array(
      'protocol' => self::Protocol,
      'method' => self::Method,
      'params' => $params
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

  static function receive(Client $client, $newContexts) {
    $newContexts = array_filter((array)$newContexts);
    if (!count($newContexts)) {
      Log::error("One or more contexts are required.");
      return \React\Promise\resolve(false);
    }
    $contexts = array_diff($newContexts, $client->contexts);
    if (!count($contexts)) {
      return \React\Promise\resolve(true);
    }
    $msg = new Execute([
      'protocol' => $client->relayProtocol,
      'method' => self::Receive,
      'params' => ['contexts' => $contexts]
    ]);
    return $client->execute($msg)->then(function ($response) use ($client, $contexts) {
      $client->contexts = array_merge($client->contexts, $contexts);
      Log::info($response->result->message);
      return true;
    }, function ($error) {
      Log::error("Receive error: {$error->message}. [code: {$error->code}]");
      return false;
    });
  }

}
