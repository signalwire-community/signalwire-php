<?php

namespace SignalWire\Relay\Calling\Actions;

use SignalWire\Relay\Calling\Results\PlayResult;
use SignalWire\Relay\Calling\Results\PlayPauseResult;
use SignalWire\Relay\Calling\Results\PlayResumeResult;

class PlayAction extends BaseAction {

  public function getResult() {
    return new PlayResult($this->component);
  }

  public function stop() {
    return $this->component->stop();
  }

  public function pause() {
    return $this->component->pause(PlayPauseResult::class);
  }

  public function resume() {
    return $this->component->resume(PlayResumeResult::class);
  }

}
