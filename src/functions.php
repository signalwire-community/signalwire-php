<?php

namespace SignalWire;

use SignalWire\Relay\Calling\RecordType;

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
