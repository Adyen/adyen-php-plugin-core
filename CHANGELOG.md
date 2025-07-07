# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/).

## [Unreleased](,https://github.com/Adyen/adyen-php-plugin-core/compare/main...dev)

## [2.1.4 - 2.1.5](https://github.com/Adyen/adyen-php-plugin-core/compare/2.1.1...2.1.2) - 2025-07-07
- Improve TaskRunner wakeup logic

## [2.1.3 - 2.1.4](https://github.com/Adyen/adyen-php-plugin-core/compare/2.1.1...2.1.2) - 2025-07-01
- Fix webhook construction bug

## [2.1.2 - 2.1.3](https://github.com/Adyen/adyen-php-plugin-core/compare/2.1.1...2.1.2) - 2025-06-17
- Enhance webhook handling for carts not paid with Adyen
- Enhance synchronous webhook handling for already processed webhooks

## [2.1.1 - 2.1.2](https://github.com/Adyen/adyen-php-plugin-core/compare/2.1.1...2.1.2) - 2025-05-15
- Fix duplicate webhook handling

## [2.1.0 - 2.1.1](https://github.com/Adyen/adyen-php-plugin-core/compare/2.1.0...2.1.1) - 2025-05-09
- Remove str_contains from WebhookController

## [2.0.0 - 2.1.0](https://github.com/Adyen/adyen-php-plugin-core/compare/2.0.0...2.1.0) - 2025-04-14
- Add compatibility with both GooglePay tx variants - googlepay and paywithgoogle
- Add support for synchronous webhooks handling

## [1.2.7 - 2.0.0](https://github.com/Adyen/adyen-php-plugin-core/compare/1.2.7...2.0.0) - 2025-04-03
- Add support for partial payments

## [1.2.6 - 1.2.7](https://github.com/Adyen/adyen-php-plugin-core/compare/1.2.6...1.2.7) - 2025-04-02
- Fix type mismatch by allowing null in getTransactionLog method

## [1.2.5 - 1.2.6](https://github.com/Adyen/adyen-php-plugin-core/compare/1.2.5...1.2.6) - 2025-01-30
- Update webhook module version

## [1.2.4 - 1.2.5](https://github.com/Adyen/adyen-php-plugin-core/compare/1.2.4...1.2.5) - 2025-01-29
- Fix filtering authorised transaction items

## [1.2.3 - 1.2.4](https://github.com/Adyen/adyen-php-plugin-core/compare/1.2.2...1.2.3) - 2025-01-22
- Rename Index class in infrastructure ORM to avoid collision with index.php files on MacOS 

## [1.2.2 - 1.2.3](https://github.com/Adyen/adyen-php-plugin-core/compare/1.2.2...1.2.3) - 2025-01-15
- Fix transaction history original reference logic

## [1.2.1 - 1.2.2](https://github.com/Adyen/adyen-php-plugin-core/compare/1.2.1...1.2.2) - 2024-12-17
- Update PHP webhook module dependency
- Add chargeback webhook handling

## [1.2.0 - 1.2.1](https://github.com/Adyen/adyen-php-plugin-core/compare/1.2.0...1.2.1) - 2024-11-28
- Add amount to update payment details response

## [1.1.13 - 1.2.0](https://github.com/Adyen/adyen-php-plugin-core/compare/1.1.13...1.2.0) - 2024-09-25
- Add support for authorization adjustment
- Add support for click to pay
- Add support for GooglePay and ApplePay guest express checkout

## [1.1.12 - 1.1.13](https://github.com/Adyen/adyen-php-plugin-core/compare/1.1.12...1.1.13) - 2024-07-16
- Fix order status transition when fail authorization webhook is received
- Make sure that webhook validation doesn't leave empty client key configuration

## [1.1.11 - 1.1.12](https://github.com/Adyen/adyen-php-plugin-core/compare/1.1.11...1.1.12) - 2024-04-15
- Fix retrieving new payment state when transaction is cancelled

## [1.1.10 - 1.1.11](https://github.com/Adyen/adyen-php-plugin-core/compare/1.1.10...1.1.11) - 2024-03-11
- Remove 'New' and 'Pending' order status from Order status map

## [1.1.9 - 1.1.10](https://github.com/Adyen/adyen-php-plugin-core/compare/1.1.9...1.1.10) - 2024-03-04
- Fix calling integration order service for updating order payment when synchronizing webhook

## [1.1.8 - 1.1.9](https://github.com/Adyen/adyen-php-plugin-core/compare/1.1.8...1.1.9) - 2024-02-26
- Modify webhook test request body
  - Add merchant reference field
  - Add validation in Synchronization service not to enqueue test webhooks

## [1.1.7 - 1.1.8](https://github.com/Adyen/adyen-php-plugin-core/compare/1.1.7...1.1.8) - 2024-02-02
- Add necessary services and test data for the E2E tests

## [1.1.6 - 1.1.7](https://github.com/Adyen/adyen-php-plugin-core/compare/1.1.6...1.1.7) - 2024-01-30
- Add setCurrencies method to PaymentMethodResponse
- Update User-Agent header in CurlHttpClient

## [1.1.5 - 1.1.6](https://github.com/Adyen/adyen-php-plugin-core/compare/1.1.5...1.1.6) - 2024-01-22
- Set default value for default payment link expiration time.
- Fix amount comparisons - switch to comparing in minor units.

## [1.1.4 - 1.1.5](https://github.com/Adyen/adyen-php-plugin-core/compare/1.1.4...1.1.5) - 2024-01-11
- Fix webhook test request body
- Add method for checking if payment response is in pending status

## [1.1.3 - 1.1.4](https://github.com/Adyen/adyen-php-plugin-core/compare/1.1.3...1.1.4) - 2023-12-29
- Fix webhook test request body

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
