<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\InfoSettings;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\Domain\InfoSettings\Services\ValidationService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Proxies\WebhookProxy;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\InfoSettings\Mocks\MockValidationService;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

/**
 * Class WebhookValidateApiTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\InfoSettings
 */
class WebhookValidateApiTest extends BaseTestCase
{
    /**
     * @var MockValidationService
     */
    private $validationService;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->validationService = new MockValidationService(
            TestServiceRegister::getService(WebhookProxy::class),
            TestServiceRegister::getService(WebhookConfigRepository::class)
        );

        TestServiceRegister::registerService(
            ValidationService::class,
            new SingleInstance(function () {
                return $this->validationService;
            })
        );
    }

    /**
     * @throws Exception
     */
    public function testValidationResponseSuccessful(): void
    {
        // Act
        $valid = AdminAPI::get()->webhookValidation('1')->validate();

        // Assert
        self::assertTrue($valid->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testValidationFailsResponseToArray(): void
    {
        // Act
        $valid = AdminAPI::get()->webhookValidation('1')->validate();

        // Assert
        self::assertEquals(['status' => false, 'message' => 'webhook.validation.fail'], $valid->toArray());
    }

    /**
     * @throws Exception
     */
    public function testValidationSuccessResponseToArray(): void
    {
        //Arrange
        $this->validationService->setMockValidationSuccess(true);

        // Act
        $valid = AdminAPI::get()->webhookValidation('1')->validate();

        // Assert
        self::assertEquals(['status' => true, 'message' => 'webhook.validation.success'], $valid->toArray());
    }

    /**
     * @throws Exception
     */
    public function testValidationReportResponseSuccessful(): void
    {
        // Act
        $report = AdminAPI::get()->webhookValidation('1')->report();

        // Assert
        self::assertTrue($report->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testValidationReportResponseToArray(): void
    {
        // Act
        $report = AdminAPI::get()->webhookValidation('1')->report();

        // Assert
        self::assertEquals(['report' => 'REPORT'], $report->toArray());
    }
}
