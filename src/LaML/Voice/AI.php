<?php

namespace SignalWire\LaML\Voice;

use Twilio\TwiML\TwiML;

class AI extends TwiML {

    /**
     * AI constructor.
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
     * @param string $voice
     */
    public function setVoice($voice): self {
        return $this->setAttribute('voice', $voice);
    }

    /**
     * Add PostPromptURL attribute.
     *
     * @param bool $postPromptURL
     */
    public function setPostPromptURL($postPromptURL): self {
        return $this->setAttribute('postPromptURL', $postPromptURL);
    }


    /**
     * Add postPromptAuthUser attribute.
     *
     * @param string $postPromptAuthUser
     */
    public function setPostPromptAuthUser($postPromptAuthUser): self {
        return $this->setAttribute('postPromptAuthUser', $postPromptAuthUser);
    }

    /**
     * Add postPromptAuthPassword attribute.
     *
     * @param string $postPromptAuthPassword
     */
    public function setPostPromptAuthPassword($postPromptAuthPassword): self {
        return $this->setAttribute('postPromptAuthPassword', $postPromptAuthPassword);
    }

    /**
     * Add hints attribute.
     *
     * @param string $hints
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

    public function languages($attributes = []): Languages {
        return $this->nest(new Languages($attributes));
    }

    public function swaig($attributes = []): SWAIG {
        return $this->nest(new SWAIG($attributes));
    }
}

class GenericPrompt extends TwiML {
    /**
     * Generic prompt constructor.
     *
     * @param string $tagName Tag name (Prompt or PostPrompt)
     * @param string $value Content of the prompt
     * @param array $attributes Optional attributes
     */
    public function __construct($tagName, $value, $attributes = []) {
        parent::__construct($tagName, $value, $attributes);
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
     * Prompt constructor.
     *
     * @param string $value Content of the prompt
     * @param array $attributes Optional attributes
     */
    public function __construct($value, $attributes = []) {
        parent::__construct('Prompt', $value, $attributes);
    }
}

class PostPrompt extends GenericPrompt {
    /**
     * PostPrompt constructor.
     *
     * @param string $value Content of the prompt
     * @param array $attributes Optional attributes
     */
    public function __construct($value, $attributes = []) {
        parent::__construct('PostPrompt', $value, $attributes);
    }
}

class Languages extends TwiML {
    /**
     * Languages constructor.
     *
     * @param array $attributes Optional attributes
     */
    public function __construct() {
        parent::__construct('Languages', "", []);
    }

    public function language($attributes = []): Language {
        return $this->nest(new Language($attributes));
    }
}

class Language extends TwiML {
    /**
     * Language constructor.
     *
     * @param array $attributes Optional attributes
     */
    public function __construct($attributes = []) {
        parent::__construct('Language', "", $attributes);
    }

    /**
     * Add code attribute.
     *
     * @param string $code 
     */
    public function setCode($code): self {
        return $this->setAttribute('code', $code);
    }

    /**
     * Add name attribute.
     *
     * @param string $name 
     */
    public function setName($name): self {
        return $this->setAttribute('name', $name);
    }

    /**
     * Add voice attribute.
     *
     * @param string $voice 
     */
    public function setVoice($voice): self {
        return $this->setAttribute('voice', $voice);
    }
}

class SWAIG extends TwiML {
    /**
     * SWAIG constructor.
     *
     * @param array $attributes Optional attributes
     */
    public function __construct() {
        parent::__construct('SWAIG', "", []);
    }

    public function defaults($attributes = []): SWAIGDefaults {
        return $this->nest(new SWAIGDefaults($attributes));
    }

    public function function($attributes = []): SWAIGFunction {
        return $this->nest(new SWAIGFunction($attributes));
    }
}

class SWAIGDefaults extends TwiML {
    /**
     * Defaults constructor.
     *
     * @param array $attributes Optional attributes
     */
    public function __construct($attributes = []) {
        parent::__construct('Defaults', "", $attributes);
    }

    /**
     * Add webHookURL attribute.
     *
     * @param string $webHookURL 
     */
    public function setWebHookURL($webHookURL): self {
        return $this->setAttribute('webHookURL', $webHookURL);
    }

    /**
     * Add webHookAuthUser attribute.
     *
     * @param string $webHookAuthUser 
     */
    public function setWebHookAuthUser($webHookAuthUser): self {
        return $this->setAttribute('webHookAuthUser', $webHookAuthUser);
    }

    /**
     * Add webHookAuthPass attribute.
     *
     * @param string $webHookAuthPass 
     */
    public function setWebHookAuthPass($webHookAuthPass): self {
        return $this->setAttribute('webHookAuthPass', $webHookAuthPass);
    }

    /**
     * Add metadata.
     *
     * @param string $name
     * @param string $value 
     */
    public function addMetaData($name, $value): self {
        $this->nest(new SWAIGMetadata($name, $value, []));
        return $this;
    }
}

class SWAIGFunction extends TwiML {
    /**
     * Function constructor.
     *
     * @param array $attributes Optional attributes
     */
    public function __construct($attributes = []) {
        parent::__construct('Function', "", $attributes);
    }

    /**
     * Add name attribute.
     *
     * @param string $name 
     */
    public function setName($name): self {
        return $this->setAttribute('name', $name);
    }

    /**
     * Add argument attribute.
     *
     * @param string $argument 
     */
    public function setArgument($argument): self {
        return $this->setAttribute('argument', $argument);
    }

    /**
     * Add purpose attribute.
     *
     * @param string $purpose 
     */
    public function setPurpose($purpose): self {
        return $this->setAttribute('purpose', $purpose);
    }

    /**
     * Add webHookURL attribute.
     *
     * @param string $webHookURL 
     */
    public function setWebHookURL($webHookURL): self {
        return $this->setAttribute('webHookURL', $webHookURL);
    }

    /**
     * Add webHookAuthUser attribute.
     *
     * @param string $webHookAuthUser 
     */
    public function setWebHookAuthUser($webHookAuthUser): self {
        return $this->setAttribute('webHookAuthUser', $webHookAuthUser);
    }

    /**
     * Add webHookAuthPass attribute.
     *
     * @param string $webHookAuthPass 
     */
    public function setWebHookAuthPass($webHookAuthPass): self {
        return $this->setAttribute('webHookAuthPass', $webHookAuthPass);
    }

    /**
     * Add metadata.
     *
     * @param string $name
     * @param string $value 
     */
    public function addMetaData($name, $value): self {
        $this->nest(new SWAIGMetadata($name, $value, []));
        return $this;
    }
}

class SWAIGMetadata extends TwiML {
    /**
     * SWAIG Metadata constructor.
     *
     * @param array $attributes Optional attributes
     */
    public function __construct($name, $value, $attributes = []) {
        parent::__construct($name, $value, $attributes);
    }
}