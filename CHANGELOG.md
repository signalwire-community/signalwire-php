# Changelog
All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
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
