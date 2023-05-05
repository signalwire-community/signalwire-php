<?php

namespace SignalWire\LaML\Voice;

class Dial extends \Twilio\TwiML\Voice\Dial {
    public function ai($attributes = []): AI {
        return $this->nest(new AI($attributes));
    }
}
