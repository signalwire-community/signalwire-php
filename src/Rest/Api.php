<?php
namespace SignalWire\Rest;

class Api extends \Twilio\Rest\Api {
  const ENV_SW_HOST = "SIGNALWIRE_API_HOSTNAME";
  public $baseUrl = '';

  public function __construct(Client $client, $domain = "") {
    parent::__construct($client);

    if ($domain == "") {
      $domain = "";
      if (isset($_ENV[self::ENV_SW_HOST]) && trim($_ENV[self::ENV_SW_HOST]) !== "") {
        $domain = trim($_ENV[self::ENV_SW_HOST]);
      } elseif (getenv(self::ENV_SW_HOST) !== false) {
        $domain = getenv(self::ENV_SW_HOST);
      } else {
        throw new \Exception("Missing SIGNALWIRE_API_HOSTNAME environment variable.");
      }
    }

    $this->baseUrl = "https://$domain";
  }
}