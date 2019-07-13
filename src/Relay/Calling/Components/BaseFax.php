<?php

namespace SignalWire\Relay\Calling\Components;

use SignalWire\Relay\Calling\Notification;
use SignalWire\Relay\Calling\FaxState;
use SignalWire\Relay\Calling\Event;

abstract class BaseFax extends Controllable {
  public $eventType = Notification::Fax;

  public $direction;
  public $identity;
  public $remoteIdentity;
  public $document;
  public $pages;

  public function notificationHandler($params) {
    $fax = $params->fax;
    $this->state = $fax->type;

    $this->completed = $this->state !== FaxState::Page;
    if ($this->completed) {
      if (isset($fax->params->success) && $fax->params->success) {
        $this->successful = true;
        $this->direction = $fax->params->direction;
        $this->identity = $fax->params->identity;
        $this->remoteIdentity = $fax->params->remote_identity;
        $this->document = $fax->params->document;
        $this->pages = $fax->params->pages;
      }
      $this->event = new Event($this->state, $fax);
    }

    if ($this->_hasBlocker() && in_array($this->state, $this->_eventsToWait)) {
      ($this->blocker->resolve)();
    }
  }
}
