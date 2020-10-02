<?php
namespace SignalWire\Rest;

class Client extends \Twilio\Rest\Client {
  const ENV_SW_SPACE = "SIGNALWIRE_SPACE_URL";
  const ENV_SW_HOSTNAME = "SIGNALWIRE_API_HOSTNAME";

  public function __construct($project, $token, Array $options = array()) {
    $accountSid = null;
    $region = null;
    $httpClient = isset($options["httpClient"]) ? $options["httpClient"] : null;
    $environment = null;
    parent::__construct($project, $token, $accountSid, $region, $httpClient, $environment);

    $domain = $this->_getHost($options);
    $this->_api = new Api($this, $domain);
  }

  public function getSignalwireDomain() {
    return $this->_api->baseUrl;
  }

  protected function getFax(): \Twilio\Rest\Fax {
    if (!$this->_fax) {
        $this->_fax = new \SignalWire\Rest\Fax($this);
    }
    return $this->_fax;
  }

  private function _getHost(Array $options = array()) {
    if (array_key_exists("signalwireSpaceUrl", $options) && trim($options["signalwireSpaceUrl"]) !== "") {
      return trim($options["signalwireSpaceUrl"]);
    } elseif ($this->_checkEnv(self::ENV_SW_SPACE)) {
      return $this->_checkEnv(self::ENV_SW_SPACE);
    } elseif ($this->_checkEnv(self::ENV_SW_HOSTNAME)) {
      return $this->_checkEnv(self::ENV_SW_HOSTNAME);
    }

    throw new \Exception("SignalWire Space URL is not configured.\nEnter your SignalWire Space domain via the SIGNALWIRE_SPACE_URL or SIGNALWIRE_API_HOSTNAME environment variables, or specifying the property \"signalwireSpaceUrl\" in the init options.");
  }

  private function _checkEnv(String $key) {
    if (isset($_ENV[$key]) && trim($_ENV[$key]) !== "") {
      return trim($_ENV[$key]);
    } elseif (getenv($key) !== false) {
      return getenv($key);
    }
    return false;
  }
}

class Fax extends \Twilio\Rest\Fax {
  public function __construct(Client $client) {
    parent::__construct($client);
    $this->baseUrl = $client->getSignalwireDomain();
  }

  protected function getV1(): \Twilio\Rest\Fax\V1 {
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
