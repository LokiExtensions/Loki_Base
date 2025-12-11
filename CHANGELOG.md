# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.13] - 11 December 2025
### Fixed
- Move dependency ComponentViewModelInterface from `Loki_Base` to `Loki_Components`

## [1.1.12] - 11 December 2025
### Fixed
- Rename `LOKI_BASE_URL` to `BASE_URL`

## [1.1.11] - 21 November 2025
### Fixed
- Add compatibility with `MageOS_AlpineLoader`
- Replace `$block->getChildHtml()` with `$childRenderer->all()` including better sorting
- Automatically sync localStorage.setItem to Alpine store

## [1.1.10] - 12 November 2025
### Fixed
- Make sure component scripts are loaded after all other scripts

## [1.1.9] - 12 November 2025
### Fixed
- Rename LokiMessageStore to Message
- Only load MageCookies if `MageOS_AlpineLocalStorage` is not enabled
- Fix wrong template variable
- Rename LokiLocalStorage to LocalStorage
- Check for duplicate `MageOS_AlpineLocalStorage`
- Move layout that is duplicate to Mage-OS Alpine to separate handles
- Remove temporarily Tailwind LESS attempt

## [1.1.8] - 03 November 2025
### Fixed
- Change z-index for messages from 100 to 5
- Update composer keywords

## [1.1.7] - 22 October 2025
### Fixed
- Do not escape `$css()` with `escapeHtmlAttr()` but `escapeHtml()`

## [1.1.6] - 09 October 2025
### Fixed
- Adjust messages under Luma from z-index:300 to 100 to prevent clash with minicart

## [1.1.5] - 08 October 2025
### Fixed
- Remove cookies by stripping all non-relevant cookie parts
- Change messages to position:sticky in Luma

## [1.1.4] - 07 October 2025
### Fixed
- Allow for JS translations of dates

## [1.1.3] - 30 September 2025
### Fixed
- Use new LOKI_THEME_URL variable in JS

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
