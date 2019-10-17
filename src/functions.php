<?php

namespace SignalWire;

use SignalWire\Relay\Calling\RecordType;
use SignalWire\Relay\Calling\PromptType;
use SignalWire\Relay\Calling\PlayType;
use SignalWire\Relay\Calling\DetectType;
use SignalWire\Relay\Calling\DetectState;
use SignalWire\Relay\Calling\TapType;

function prepareConnectParams(Array $params, String $defaultFrom, Int $defaultTimeout): Array {
  $devices = [];
  $ringback = [];
  if (count($params) === 1 && isset($params[0]['devices'])) {
    $devices = $params[0]['devices'];
    if (isset($params[0]['ringback'])) {
      $ringback = destructMedia($params[0]['ringback']);
    }
  } else {
    $devices = $params;
  }
  return [
    reduceConnectParams($devices, $defaultFrom, $defaultTimeout),
    $ringback
  ];
}

function reduceConnectParams(Array $devices, String $defaultFrom, Int $defaultTimeout, $nested = false) {
  $final = [];
  foreach ($devices as $d) {
    $tmp = [];
    if (is_array($d) && isset($d[0])) {
      $tmp = reduceConnectParams($d, $defaultFrom, $defaultTimeout, true);
    } else {
      $tmp = [
        "type" => $d["type"],
        "params" => [
          "from_number" => isset($d["from"]) ? $d["from"] : $defaultFrom,
          "to_number" => isset($d["to"]) ? $d["to"] : "",
          "timeout" => isset($d["timeout"]) ? $d["timeout"] : $defaultTimeout
        ]
      ];
    }

    $nested || isset($tmp[0]) ? array_push($final, $tmp) : array_push($final, [$tmp]);
  }

  return $final;
}

function checkWebSocketHost(String $host): String {
  $protocol = preg_match("/^(ws|wss):\/\//", $host) ? '' : 'wss://';
  return $protocol . $host;
}

function prepareRecordParams(Array $params): Array {
  $type = RecordType::Audio; // Default to audio
  $subParams = isset($params['audio']) ? $params['audio'] : $params;
  unset($params['type'], $params['audio']);
  $subParams = $subParams + $params;
  $record = [ $type => $subParams ];
  return $record;
}

function destructMedia(Array $media): Array {
  $type = isset($media['type']) ? $media['type'] : '';
  $params = isset($media['params']) ? $media['params'] : [];
  unset($media['type'], $media['params']);
  $params = $params + $media;
  return ['type' => $type, 'params' => $params];
}

function preparePlayParams(Array $params): Array {
  $volume = 0;
  if (count($params) === 1 && isset($params[0]['media'])) {
    $mediaList = $params[0]['media'];
    $volume = isset($params[0]['volume']) ? $params[0]['volume'] : 0;
  } else {
    $mediaList = $params;
  }
  $mediaToPlay = [];
  foreach($mediaList as $media) {
    if (is_array($media)) {
      array_push($mediaToPlay, destructMedia($media));
    }
  }
  return [$mediaToPlay, $volume];
}

function preparePlayAudioParams($params): Array {
  if (gettype($params) === 'string') {
    return [$params, 0];
  } elseif (gettype($params) === 'array') {
    $url = isset($params['url']) ? $params['url'] : '';
    $volume = isset($params['volume']) ? $params['volume'] : '';
    return [$url, $volume];
  }
  return ['', 0];
}

function preparePlayRingtoneParams($params): Array {
  $volume = isset($params['volume']) ? $params['volume'] : 0;
  unset($params['volume']);
  if (isset($params['duration'])) {
    $params['duration'] = (float)$params['duration'];
  }
  return [$params, $volume];
}

function preparePromptParams(Array $params, Array $mediaList = []): Array {
  $digits = isset($params[PromptType::Digits]) ? $params[PromptType::Digits] : [];
  $speech = isset($params[PromptType::Speech]) ? $params[PromptType::Speech] : [];
  $mediaToPlay = isset($params['media']) ? $params['media'] : $mediaList;
  unset($params[PromptType::Digits], $params[PromptType::Speech], $params['media']);
  if (!count($digits)) {
    if (isset($params['digits_max'])) {
      $digits['max'] = $params['digits_max'];
    }
    if (isset($params['digits_terminators'])) {
      $digits['terminators'] = $params['digits_terminators'];
    }
    if (isset($params['digits_timeout'])) {
      $digits['digit_timeout'] = $params['digits_timeout']; // warn: 'digits_' vs 'digit_' for consistency
    }
  }
  if (!count($speech)) {
    if (isset($params['end_silence_timeout'])) {
      $speech['end_silence_timeout'] = $params['end_silence_timeout'];
    }
    if (isset($params['speech_timeout'])) {
      $speech['speech_timeout'] = $params['speech_timeout'];
    }
    if (isset($params['speech_language'])) {
      $speech['language'] = $params['speech_language'];
    }
    if (isset($params['speech_hints'])) {
      $speech['hints'] = $params['speech_hints'];
    }
  }
  $collect = [];
  if (isset($params['initial_timeout'])) {
    $collect['initial_timeout'] = $params['initial_timeout'];
  }
  if (isset($params['partial_results'])) {
    $collect['partial_results'] = $params['partial_results'];
  }
  $type = isset($params['type']) ? $params['type'] : '';
  if (count($digits)) {
    $collect[PromptType::Digits] = $digits;
  } elseif ($type == PromptType::Digits || $type == 'both') {
    $collect[PromptType::Digits] = new \stdClass;
  }
  if (count($speech)) {
    $collect[PromptType::Speech] = $speech;
  } elseif ($type == PromptType::Speech || $type == 'both') {
    $collect[PromptType::Speech] = new \stdClass;
  }
  $volume = isset($params['volume']) ? $params['volume'] : 0;
  list($play) = preparePlayParams($mediaToPlay);
  return [$collect, $play, $volume];
}

function preparePromptAudioParams(Array $params, String $url = ''): Array {
  $url = isset($params['url']) ? $params['url'] : $url;
  unset($params['url']);
  $params['media'] = [
    ['type' => PlayType::Audio, 'params' => ['url' => $url]]
  ];
  return $params;
}

function preparePromptTTSParams(Array $params, Array $ttsOptions = []): Array {
  $keys = ['text', 'language', 'gender'];
  foreach ($keys as $key) {
    if (isset($params[$key])) {
      $ttsOptions[$key] = $params[$key];
      unset($params[$key]);
    }
  }
  $params['media'] = [
    ['type' => PlayType::TTS, 'params' => $ttsOptions]
  ];
  return $params;
}

function preparePromptRingtoneParams(Array $params): Array {
  $mediaParams = [];
  if (isset($params['name'])) {
    $mediaParams['name'] = $params['name'];
    unset($params['name']);
  }
  if (isset($params['duration'])) {
    $mediaParams['duration'] = (float)$params['duration'];
    unset($params['duration']);
  }
  $params['media'] = [
    ['type' => PlayType::Ringtone, 'params' => $mediaParams]
  ];
  return $params;
}

function prepareDetectParams(Array $params) {
  $timeout = isset($params['timeout']) ? $params['timeout'] : null;
  $type = isset($params['type']) ? $params['type'] : null;
  $waitForBeep = isset($params['wait_for_beep']) ? $params['wait_for_beep'] : false;
  unset($params['type'], $params['timeout'], $params['wait_for_beep']);
  $detect = ['type' => $type, 'params' => $params];

  return [$detect, $timeout, $waitForBeep];
}

function prepareDetectFaxParamsAndEvents(array $params) {
  $params['type'] = DetectType::Fax;
  list($detect, $timeout) = prepareDetectParams($params);
  $faxEvents = [DetectState::CED, DetectState::CNG];
  $events = [];
  $tone = isset($detect['params']['tone']) ? $detect['params']['tone'] : null;
  if ($tone && in_array($tone, $faxEvents)) {
    $detect['params'] = ['tone' => $tone];
    array_push($events, $tone);
  } else {
    $detect['params'] = [];
    $events = $faxEvents; // Both CED & CNG
  }

  return [$detect, $timeout, $events];
}

function prepareTapParams(array $params, array $deviceParams = []) {
  $tapParams = [];
  if (isset($params['audio_direction'])) {
    $tapParams['direction'] = $params['audio_direction'];
  } elseif (isset($params['direction'])) {
    $tapParams['direction'] = $params['direction'];
  }
  $tap = ['type' => TapType::Audio, 'params' => $tapParams];

  $device = ['type' => '', 'params' => []];
  if (isset($deviceParams['type'])) {
    $device['type'] = $deviceParams['type'];
    unset($deviceParams['type']);
  } elseif (isset($params['target_type'])) {
    $device['type'] = $params['target_type'];
  }

  if (isset($params['target_addr'])) {
    $deviceParams['addr'] = $params['target_addr'];
  }
  if (isset($params['target_port'])) {
    $deviceParams['port'] = $params['target_port'];
  }
  if (isset($params['target_ptime'])) {
    $deviceParams['ptime'] = $params['target_ptime'];
  }
  if (isset($params['target_uri'])) {
    $deviceParams['uri'] = $params['target_uri'];
  }
  if (isset($params['rate'])) {
    $deviceParams['rate'] = $params['rate'];
  }
  if (isset($params['codec'])) {
    $deviceParams['codec'] = $params['codec'];
  }
  $device['params'] = $deviceParams;

  return [$tap, $device];
}
