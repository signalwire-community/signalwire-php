<?php
namespace SignalWire\Rest;

class Api extends \Twilio\Rest\Api {
  public $baseUrl = '';

  public function __construct(Client $client, String $domain) {
    parent::__construct($client);

    $this->baseUrl = "https://$domain";
  }
}
