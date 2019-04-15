<?php
namespace SignalWire\Relay;
use SignalWire\Messages\Execute;
use SignalWire\Log;

abstract class BaseRelay {
  const SetupProtocol = 'signalwire';
  const SetupMethod = 'setup';
  const SetupChannels = array('notifications');

  protected $protocol;
  protected $ready;
  protected $client;

  abstract function getServiceName(): String;
  abstract function notificationHandler($notification): void;

  public function __construct(Client $client) {
    $this->client = $client;
    $this->ready = new \React\Promise\Promise(function (callable $resolve) use ($client) {
      $msg = new Execute(array(
        'protocol' => self::SetupProtocol,
        'method' => self::SetupMethod,
        'params' => array('service' => $this->getServiceName())
      ));
      $client->execute($msg)->then(
        function($response) use ($client, $resolve) {
          $client->subscribe($response->result->protocol, self::SetupChannels, [$this, "notificationHandler"])->then(
            function($response) use ($resolve) {
              $this->protocol = $response->protocol;
              call_user_func($resolve, $this->protocol);
            }, function($error) {
              // TODO: throw exception
              Log::warning('Setup error:');
              print_r($error);
            }
          );
        }, function($error) {
          // TODO: throw exception
          Log::warning('Setup error:');
          print_r($error);
        }
      );
    });
  }
}
