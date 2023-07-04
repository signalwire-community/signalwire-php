<?php

namespace SignalWire\Rest\Api;

class V2010 extends \Twilio\Rest\Api\V2010 {
  /**
   * @return AccountContext Account provided as the authenticating account
   */
  protected function getAccount(): \Twilio\Rest\Api\V2010\AccountContext {
    if (!$this->_account) {
      $this->_account = new \SignalWire\Rest\Api\V2010\AccountContext(
        $this,
        $this->domain->getClient()->getAccountSid()
      );
    }
    return $this->_account;
  }
}
