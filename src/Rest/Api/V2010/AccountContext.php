<?php

namespace SignalWire\Rest\Api\V2010;

class AccountContext extends \Twilio\Rest\Api\V2010\AccountContext {
  /**
   * Access the calls
   */
  protected function getCalls(): \Twilio\Rest\Api\V2010\Account\CallList {
    if (!$this->_calls) {
      $this->_calls = new \SignalWire\Rest\Api\V2010\Account\CallList(
        $this->version,
        $this->solution['sid']
      );
    }

    return $this->_calls;
  }
}
