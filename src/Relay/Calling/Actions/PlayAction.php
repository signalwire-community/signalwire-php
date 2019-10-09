<?php

namespace SignalWire\Relay\Calling\Actions;

use SignalWire\Relay\Calling\Results\PlayResult;
use SignalWire\Relay\Calling\Results\PlayPauseResult;
use SignalWire\Relay\Calling\Results\PlayResumeResult;
use SignalWire\Relay\Calling\Results\PlayVolumeResult;

class PlayAction extends BaseAction {

  public function getResult() {
    return new PlayResult($this->component);
  }

  public function stop() {
    return $this->component->stop();
  }

  public function pause() {
    return $this->component->pause()->then(function($result) {
      return new PlayPauseResult($result);
    });
  }

  public function volume($value) {
    return $this->component->volume($value)->then(function($result) {
      return new PlayVolumeResult($result);
    });
  }

  public function resume() {
    return $this->component->resume()->then(function($result) {
      return new PlayResumeResult($result);
    });
  }

}
