<?php
namespace SignalWire\Relay\Tasking;

use SignalWire\Relay\Constants;
use GuzzleHttp\Client;

final class Task {

  public $host;
  public $project;
  public $token;
  public $_httpClient;

  public function __construct(string $project, string $token) {
    $this->host = 'https://' . Constants::Host;
    $this->project = $project;
    $this->token = $token;


    $this->_httpClient = new Client([
      'timeout' => 5,
      'defaults' => [
        'auth' => [ $this->project, $this->token ]
      ]
    ]);

  }

  public function deliver(string $context, $msg) {
    $params = [
      'context' => $context,
      'message' => $msg
    ];
    // print_r($params);
    try {
      $uri = "{$this->host}/api/relay/private/tasks";
      $response = $this->_httpClient->request('POST', $uri, ['json' => $params]);
      $body = json_decode($response->getBody());
      // print_r($body);

      return true;
    } catch (\Throwable $th) {
      // print_r($th->getRequest());
      echo PHP_EOL . $th->getMessage() . PHP_EOL;

      return false;
    }
  }
}
