<?php

namespace SignalWire;

use SignalWire\Relay\Calling\RecordType;
use SignalWire\Relay\Calling\PromptType;

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

function preparePlayParams(Array $mediaList): Array {
  $mediaToPlay = [];
  foreach($mediaList as $media) {
    $type = isset($media['type']) ? $media['type'] : '';
    $params = isset($media['params']) ? $media['params'] : [];
    unset($media['type'], $media['params']);
    $params = $params + $media;
    array_push($mediaToPlay, ['type' => $type, 'params' => $params]);
  }
  return $mediaToPlay;
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
  if (count($digits)) {
    $collect[PromptType::Digits] = $digits;
  }
  if (count($speech)) {
    $collect[PromptType::Speech] = $speech;
  }
  return [$collect, preparePlayParams($mediaToPlay)];
}

function preparePromptAudioParams(Array $params, String $url): Array {
  $url = isset($params['url']) ? $params['url'] : $url;
  unset($params['url']);
  $params['media'] = [
    ['type' => PlayType::Audio, 'params' => ['url' => $url]]
  ];
  return $params;
}
