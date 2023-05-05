<?php

namespace SignalWire\LaML\Voice;

class Connect extends \Twilio\TwiML\Voice\Connect {
    public function ai($attributes = []): AI {
        return $this->nest(new AI($attributes));
    }
}
