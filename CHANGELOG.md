# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/).

## [Unreleased](https://github.com/Adyen/adyen-php-plugin-core/compare/main...dev)

## [1.1.2 - 1.1.3](https://github.com/Adyen/adyen-php-plugin-core/compare/1.1.2...1.1.3) - 2023-12-27
- Add event codes when webhook is registered

## [1.1.1 - 1.1.2](https://github.com/Adyen/adyen-php-plugin-core/compare/1.1.1...1.1.2) - 2023-12-25
- Fix Adyen Recurring API URL

## [1.1.0 - 1.1.1](https://github.com/Adyen/adyen-php-plugin-core/compare/1.1.0...1.1.1) - 2023-12-20
- Add support for Alma payment method
- Add pagination when fetching merchants

## [1.0.7 - 1.1.0](https://github.com/Adyen/adyen-php-plugin-core/compare/1.0.7...1.1.0) - 2023-12-05
- Breaking:
  - Pay by link logic implemented
  - Recurring payment logic implemented
- Add compatability for PHP 8.1

## [1.0.6 - 1.0.7](https://github.com/Adyen/adyen-php-plugin-core/compare/1.0.6...1.0.7) - 2023-11-28
- Refactor message when unsupported webhook event code is processed 
- Add compatability for HTTP Authorisation with PHP in CGI-mode

## [1.0.5 - 1.0.6](https://github.com/Adyen/adyen-php-plugin-core/compare/1.0.5...1.0.6) - 2023-11-14
- Refactor retry mechanism in OrderUpdate task to handle exceptions
- Fix Transaction details when payment code is invalid

## [1.0.4 - 1.0.5](https://github.com/Adyen/adyen-php-plugin-core/compare/1.0.4...1.0.5) - 2023-11-09
- Add retry mechanism when checking if order exists in OrderUpdate task

## [1.0.3 - 1.0.4](https://github.com/Adyen/adyen-php-plugin-core/compare/1.0.3...1.0.4) - 2023-11-02
- Breaking: 
  - Integration Application info processor deleted
  - Application info processor implemented in core

## [1.0.0](https://github.com/Adyen/adyen-php-plugin-core/releases/tag/1.0.0) - 2023-08-14
- First stable release
