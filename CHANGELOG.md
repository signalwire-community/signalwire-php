# Changelog
All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.3.10] - 2022-01-10
### Fixed
- Fixed `SignalWire\LaML\MessageResponse` namespace and introduced `SignalWire\LaML\MessagingResponse` for better compatibility.

## [2.3.9] - 2021-09-23
### Updated
- Update compatibility SDK versions.
- Add requirement to PHP `^7`.

## [2.3.8] - 2020-10-14
### Updated
- Relax Guzzle version requirements and allow Guzzle 7.

## [2.3.7] - 2020-09-05
### Updated
- Updated Twilio version to `6.10.4`
- Added new test file and examples
- Added VoiceResponse, FaxResponse and MessageResponse

## [2.3.6] - 2020-04-23
### Changed
- Loosened the version requirements for `ramsey/uuid`. Allowed versions are `^3.8 || ^4.0`

## [2.3.5] - 2020-03-11
### Fixed
- Handle Blade timeout response and randomize reconnection attempts.

## [2.3.4] - 2020-01-31
### Changed
- Loosened the version requirements for `monolog/monolog`. Allowed versions are `^1.24 || ^2.0`

## [2.3.3] - 2020-01-09
### Fixed
- LaML engine

## [2.3.2] - 2019-12-16
### Added
- Call `disconnect()` method.

### Fixed
- Set `peer` property on the connected Call [#101](https://github.com/signalwire/signalwire-php/issues/101)

## [2.3.1] - 2019-11-04
### Fixed
- Reconnect and restore previous protocol issue.

## [2.3.0] - 2019-10-22
### Added
- Add `getUrl()` method to `RecordAction` object.
- Add methods to `pause` and `resume` a PlayAction.
- Ability to set volume playback on `play` and `prompt` methods, or through the asynchronous `PlayAction` and `PromptAction` objects.
- Add `playRingtone` and `playRingtoneAsync` methods to simplify play a ringtone.
- Add `promptRingtone` and `promptRingtoneAsync` methods to simplify play a ringtone.
- Support `ringback` option on `connect` and `connectAsync` methods.

## [2.2.0] - 2019-09-09
### Changed
- Minor change at the lower level APIs: using `calling.` instead of `call.` prefix for calling methods.
- Flattened parameters for _record_, _play_, _prompt_, _detect_ and _tap_ calling methods.

### Added
- New methods to perform answering machine detection: `amd` (alias to `detectAnsweringMachine`) and `amdAsync` (alias to `detectAnsweringMachineAsync`).

### Deprecated
- Deprecated the following methods on Call: `detectHuman`, `detectHumanAsync`, `detectMachine`, `detectMachineAsync`.

### Added
- Methods to send digits on a Call: `sendDigits`, `sendDigitsAsync`.

## [2.1.0] - 2019-07-30
### Added
- Create your own Relay Tasks and enable `onTask` method on RelayConsumer to receive/handle them.
- Methods to start a detector on a Call: `detect`, `detectAsync`, `detectHuman`, `detectHumanAsync`, `detectMachine`, `detectMachineAsync`, `detectFax`, `detectFaxAsync`, `detectDigit`, `detectDigitAsync`
- Methods to tap media in a Call: `tap` and `tapAsync`
- Support for Relay Messaging

### Fixed
- Possible issue on WebSocket reconnect due to a race condition on the EventLoop.

## [2.0.0] - 2019-07-16
### Added
- Add support for faxing. New call methods: `faxReceive`, `faxReceiveAsync`, `faxSend`, `faxSendAsync`.

## [2.0.0-RC1] - 2019-07-10
### Added
- Released new Relay Client interface.
- Add RelayConsumer.
- Handle SIGINT/SIGTERM signals.
- Add Relay calling `waitFor`, `waitForRinging`, `waitForAnswered`, `waitForEnding`, `waitForEnded` methods.
### Fixed
- Default React EventLoop

## [1.4.1]
### Fixed
- Fix bug handling connect notifications.

## [1.4.0]
### Added
- Ability to set a custom `\React\EventLoop` in RelayClient.

## [1.3.0]
### Added
- Call `connect()` method.
- Call `record()` method.
- Call `playMedia()`, `playAudio()`, `playTTS()`, `playSilence()` methods.
- Call `playMediaAndCollect()`, `playAudioAndCollect()`, `playTTSAndCollect()`, `playSilenceAndCollect()` methods.
- Expose Call `play.*`, `record.*`, `collect` events.

## [1.2.1]
### Fixed
- Add websocket host protocol and path automatically.

## [1.2.0]
### Added
- Relay SDK to connect and use SignalWire's Relay APIs.

## [1.1.1]
### Added
- Ability to set SignalWire Space URL in `SignalWire\Rest\Client` constructor via `signalwireSpaceUrl` key.
- Support SIGNALWIRE_SPACE_URL env variable.

## [1.1.0]
### Added
- Fax support

## [1.0.0]

Initial release

<!---
### Added
### Changed
### Removed
### Fixed
### Security
-->
