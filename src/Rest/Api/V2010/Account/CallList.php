<?php

namespace SignalWire\Rest\Api\V2010\Account;

use Twilio\Values;
use Twilio\Serialize;

class CallList extends \Twilio\Rest\Api\V2010\Account\CallList {
  /**
   * Create the CallInstance
   *
   * @param string $to Phone number, SIP address, or client identifier to call
   * @param string $from Twilio number from which to originate the call
   * @param array|Options $options Optional Arguments
   * @return CallInstance Created CallInstance
   * @throws TwilioException When an HTTP error occurs.
   */
  public function create(string $to, string $from, array $options = []): \Twilio\Rest\Api\V2010\Account\CallInstance {
    $options = new Values($options);

    $data = Values::of([
      'To' => $to,
      'From' => $from,
      'Url' => $options['url'],
      'Twiml' => $options['twiml'],
      'ApplicationSid' => $options['applicationSid'],
      'Method' => $options['method'],
      'FallbackUrl' => $options['fallbackUrl'],
      'FallbackMethod' => $options['fallbackMethod'],
      'StatusCallback' => $options['statusCallback'],
      'StatusCallbackEvent' => Serialize::map($options['statusCallbackEvent'], function ($e) {
        return $e;
      }),
      'StatusCallbackMethod' => $options['statusCallbackMethod'],
      'SendDigits' => $options['sendDigits'],
      'Timeout' => $options['timeout'],
      'Record' => Serialize::booleanToString($options['record']),
      'RecordingChannels' => $options['recordingChannels'],
      'RecordingStatusCallback' => $options['recordingStatusCallback'],
      'RecordingStatusCallbackMethod' => $options['recordingStatusCallbackMethod'],
      'SipAuthUsername' => $options['sipAuthUsername'],
      'SipAuthPassword' => $options['sipAuthPassword'],
      'MachineDetection' => $options['machineDetection'],
      'MachineDetectionTimeout' => $options['machineDetectionTimeout'],
      'RecordingStatusCallbackEvent' => Serialize::map($options['recordingStatusCallbackEvent'], function ($e) {
        return $e;
      }),
      'Trim' => $options['trim'],
      'CallerId' => $options['callerId'],
      'MachineDetectionSpeechThreshold' => $options['machineDetectionSpeechThreshold'],
      'MachineDetectionSpeechEndThreshold' => $options['machineDetectionSpeechEndThreshold'],
      'MachineDetectionSilenceTimeout' => $options['machineDetectionSilenceTimeout'],
      'AsyncAmd' => $options['asyncAmd'],
      'AsyncAmdStatusCallback' => $options['asyncAmdStatusCallback'],
      'AsyncAmdStatusCallbackMethod' => $options['asyncAmdStatusCallbackMethod'],
      'AsyncAmdPartialResults' => $options['asyncAmdPartialResults'],
      'Byoc' => $options['byoc'],
      'CallReason' => $options['callReason'],
      'CallToken' => $options['callToken'],
      'RecordingTrack' => $options['recordingTrack'],
      'TimeLimit' => $options['timeLimit'],
    ]);

    $payload = $this->version->create('POST', $this->uri, [], $data);

    return new \SignalWire\Rest\Api\V2010\Account\CallInstance(
      $this->version,
      $payload,
      $this->solution['accountSid']
    );
  }
}
