<?php
namespace SignalWire\Rest;

class Client extends \Twilio\Rest\Client {
  public function __construct(...$args) {
    $domain = $args[2];
    parent::__construct(...$args);

    $this->_api = new Api($this, $domain);
  }

  public function getSignalwireDomain() {
    return $this->_api->baseUrl;
  }

  protected function getFax() {
    if (!$this->_fax) {
        $this->_fax = new \SignalWire\Rest\Fax($this);
    }
    return $this->_fax;
  }
}

class Fax extends \Twilio\Rest\Fax {
  public function __construct(Client $client) {
    parent::__construct($client);
    $this->baseUrl = $client->getSignalwireDomain();
  }

  protected function getV1() {
    if (!$this->_v1) {
        $this->_v1 = new \SignalWire\Rest\V1($this);
    }
    return $this->_v1;
  }
  
}

class V1 extends \Twilio\Rest\Fax\V1 {
  protected $_faxes = null;
  /**
   * Construct the V1 version of Fax
   * 
   * @param \Twilio\Domain $domain Domain that contains the version
   * @return \Twilio\Rest\Fax\V1 V1 version of Fax
   */
  public function __construct(Fax $domain) {
      parent::__construct($domain);
      $this->version = '2010-04-01/Accounts/' . $domain->client->username;
  }
}
