<?php
namespace SignalWire\Rest;

class Api extends \Twilio\Rest\Api {
  public $baseUrl = '';

  public function __construct(Client $client, String $domain) {
    parent::__construct($client);

    $this->baseUrl = "https://$domain";
  }

  /**
   * @return V2010 Version v2010 of api
   */
  protected function getV2010(): \Twilio\Rest\Api\V2010 {
    if (!$this->_v2010) {
      $this->_v2010 = new \SignalWire\Rest\Api\V2010($this);
    }
    return $this->_v2010;
  }
}