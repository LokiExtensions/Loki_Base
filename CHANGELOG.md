# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.2] - 30 September 2025
### Fixed
- Remove block cache lifetime

## [1.1.1] - 29 September 2025
### Fixed
- When removing cookies, ignore the domain-value

## [1.1.0] - 23 September 2025
### Added
- Add Loki Component exception as complex message
- Move messages timeout configuration from `Loki_Components` to `Loki_Base`
- Add custom Block class that adds layout-name to cache key info
- Move frontend assets to base to allow for admin features
- Add custom event "loki:init:localstorage-store"

### Fixed
- Remove redundant CSS classes from icon containers
- Reset message timeout to 5000
- Rename `loki.script` from container to block to allow caching
- Skip handling messages via cookies and localStorage under Hyva
- Rewrite transfer of global messages from components to be a lot simpler
- Rename loki-directive to loki.script.directive
- Rename loki-store to loki.script.store
- Rename loki-components to loki.script.component
- Rename loki-component-types to loki.script.component-type
- Rename loki-component-partials to loki.script.component-partial
- Cleanup duplicate containers, move to `Loki_Base`

## [1.0.4] - 17 September 2025
### Fixed
- Re-add styling of buttons

## [1.0.3] - 17 September 2025
### Fixed
- Really move all LESS away from CSS folder

## [1.0.2] - 17 September 2025
### Fixed
- Move experimental LESS sources away from compilation
- Remove console.log message
- Remove dep with `Loki_Components`

## [1.0.1] - 17 September 2025
### Fixed
- Disable Tailwind for LESS for now (experimental)

## [1.0.0] - 17 September 2025
### Fixed
- Add LESS sources
- Prevent weird mage-messages cookie value from breaking things
- New ViewModel
- Properly implement section invalidation from Luma customerData JS
- Always use get('messages') when retrieving message section from store
- Remove expired sections in localStorage
- Remove messages with ESC
- Add box shadow to messages
- Sef long lifetime on `user_allowed_save_cookie` cookie
- Fix CSP compliance of message timeout
- Better notice colors under Luma
- Better API for cookies and messaging
- Fix mobile menu
- Correct padding of message close button under Luma
- Set timeout to 10000
- Move LokiComponents global messages to regular messages template
- Add to both Hyva and Luma
- Add SVG and click button to remove message
- Add close X
- Autoremove messages or double-click to remove
- Copy generic CI/CD files
- Adding all files to git via Yireo Command

## [0.0.1] - 13 September 2025
### Added
- Initial release
