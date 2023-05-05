<?php

namespace SignalWire\LaML\Voice;

use Twilio\TwiML\TwiML;

class AI extends TwiML {

    /**
     * Conference constructor.
     *
     * @param array $attributes Optional attributes
     */
    public function __construct($attributes = []) {
        parent::__construct('AI', "", $attributes);
    }

    /**
     * Add Engine attribute.
     *
     * @param string $engine 
     */
    public function setEngine($engine): self {
        return $this->setAttribute('engine', $engine);
    }

    /**
     * Add Voice attribute.
     *
     * @param string $voice Play beep when joining
     */
    public function setVoice($voice): self {
        return $this->setAttribute('voice', $voice);
    }

    /**
     * Add PostPromptURL attribute.
     *
     * @param bool $postPromptURL Start the conference on enter
     */
    public function setPostPromptURL($postPromptURL): self {
        return $this->setAttribute('postPromptURL', $postPromptURL);
    }


    /**
     * Add postPromptAuthUser attribute.
     *
     * @param string $postPromptAuthUser Start the conference on enter
     */
    public function setPostPromptAuthUser($postPromptAuthUser): self {
        return $this->setAttribute('postPromptAuthUser', $postPromptAuthUser);
    }

    /**
     * Add postPromptAuthPassword attribute.
     *
     * @param string $postPromptAuthPassword Start the conference on enter
     */
    public function setPostPromptAuthPassword($postPromptAuthPassword): self {
        return $this->setAttribute('postPromptAuthPassword', $postPromptAuthPassword);
    }

    /**
     * Add hints attribute.
     *
     * @param string $hints Start the conference on enter
     */
    public function setHints($hints): self {
        return $this->setAttribute('hints', $hints);
    }

    public function prompt($value, $attributes = []): Prompt {
        return $this->nest(new Prompt($value, $attributes));
    }

    public function postPrompt($value, $attributes = []): PostPrompt {
        return $this->nest(new PostPrompt($value, $attributes));
    }
}

class GenericPrompt extends TwiML {
    /**
     * Conference constructor.
     *
     * @param string $name Conference name
     * @param array $attributes Optional attributes
     */
    public function __construct($tagName, $name, $attributes = []) {
        parent::__construct($tagName, $name, $attributes);
    }

    /**
     * Add temperature attribute.
     *
     * @param float $temperature 
     */
    public function setTemperature($temperature): self {
        return $this->setAttribute('temperature', $temperature);
    }

    /**
     * Add topP attribute.
     *
     * @param float $topP 
     */
    public function setTopP($topP): self {
        return $this->setAttribute('topP', $topP);
    }

    /**
     * Add confidence attribute.
     *
     * @param float $confidence 
     */
    public function setConfidence($confidence): self {
        return $this->setAttribute('confidence', $confidence);
    }

    /**
     * Add bargeConfidence attribute.
     *
     * @param float $bargeConfidence 
     */
    public function setBargeConfidence($bargeConfidence): self {
        return $this->setAttribute('bargeConfidence', $bargeConfidence);
    }

    /**
     * Add presencePenalty attribute.
     *
     * @param float $presencePenalty 
     */
    public function setPresencePenalty($presencePenalty): self {
        return $this->setAttribute('presencePenalty', $presencePenalty);
    }

    /**
     * Add frequencyPenalty attribute.
     *
     * @param float $frequencyPenalty 
     */
    public function setFrequencyPenalty($frequencyPenalty): self {
        return $this->setAttribute('frequencyPenalty', $frequencyPenalty);
    }
}

class Prompt extends GenericPrompt {
    /**
     * Conference constructor.
     *
     * @param string $name Conference name
     * @param array $attributes Optional attributes
     */
    public function __construct($value, $attributes = []) {
        parent::__construct('Prompt', $value, $attributes);
    }
}

class PostPrompt extends GenericPrompt {
    /**
     * Conference constructor.
     *
     * @param string $name Conference name
     * @param array $attributes Optional attributes
     */
    public function __construct($value, $attributes = []) {
        parent::__construct('PostPrompt', $value, $attributes);
    }
}