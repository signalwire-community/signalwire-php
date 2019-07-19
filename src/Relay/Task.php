<?php
namespace SignalWire\Relay;

use SignalWire\Relay\Constants;
use GuzzleHttp\Client;

final class Task {

  public $host;
  public $project;
  public $token;
  public $_httpClient;

  public function __construct(string $project, string $token) {
    $this->host = Constants::Host;
    $this->project = $project;
    $this->token = $token;
    $this->_httpClient = new Client(['timeout' => 5]);
  }

  public function deliver(string $context, $message) {
    $params = [ 'context' => $context, 'message' => $message ];
    try {
      $uri = "https://{$this->host}/api/relay/rest/tasks";
      $response = $this->_httpClient->request('POST', $uri, [
        'auth' => [$this->project, $this->token],
        'json' => $params
      ]);
      return $response->getStatusCode() === 204;
    } catch (\Throwable $th) {
      echo PHP_EOL . $th->getMessage() . PHP_EOL;
      return false;
    }
  }
}
