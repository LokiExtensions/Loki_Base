# Loki_Base

<!-- badges.specs.start -->
![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/loki/magento2-base)
<!-- badges.specs.end -->

> This Magento 2 module forms the base of various other Loki solutions: Loki Checkout, Loki Admin Components, Loki Theme for Luma. It adds Alpine.js to the page, adds container-blocks for other scripts to be added, it adds its own frontend messaging component and its adds an Alpine store for both localStorage and messaging.

Notice: Most likely you do not install this module on its own, but require it through other modules.

## Installation
```bash
composer require loki/magento2-base
bin/magento module:enable Loki_Base
```

## Current status

<!-- badges.test.start -->
![Static Tests](https://img.shields.io/github/actions/workflow/status/LokiExtensions/Loki_Base/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/LokiExtensions/Loki_Base/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/LokiExtensions/Loki_Base/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/LokiExtensions/Loki_Base/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/LokiExtensions/Loki_Base/compile.yml?label=compile)
<!-- badges.test.end -->
