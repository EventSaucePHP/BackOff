# Changelog

## 1.2.0 - 2023-09-17

### Changed

- Minimum PHP version is now 8.1

### Added

- Support for infinite retries on any strategy (use `-1`).
- Jitter strategy that jitters after a threshold (`JitterAfterThreashold`).
- A task runner that uses a back-off strategy for retries (`BackOffRunner`).

## 1.1.1 - 2022-07-19

### Fixed

- Cleaned up imports
- Made static variable static

## 1.1.0 - 2021-04-18

### Added

* Added Jitters
* Added Fibonacci based strategy

## 1.0.0 - 2021-04-18

Initial release
