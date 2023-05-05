<?php
namespace SignalWire\LaML;

class VoiceResponse extends \Twilio\TwiML\VoiceResponse {
    /**
     * Add Connect child.
     *
     * @param array $attributes Optional attributes
     * @return Voice\Connect Child element.
     */
    public function connect($attributes = []): Voice\Connect {
        return $this->nest(new Voice\Connect($attributes));
    }
}
