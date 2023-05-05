<?php
namespace SignalWire\LaML;

class VoiceResponse extends \Twilio\TwiML\VoiceResponse {
    /**
     * Add Dial child.
     *
     * @param string $number Phone number to dial
     * @param array $attributes Optional attributes
     * @return Voice\Dial Child element.
     */
    public function dial($number = null, $attributes = []): Voice\Dial {
        return $this->nest(new Voice\Dial($number, $attributes));
    }
}
