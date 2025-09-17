# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
