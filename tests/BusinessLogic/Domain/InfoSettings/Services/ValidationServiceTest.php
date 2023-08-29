<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\InfoSettings\Services;

use Adyen\Core\BusinessLogic\Domain\InfoSettings\Services\ValidationService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Proxies\WebhookProxy;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\BusinessLogic\Domain\InfoSettings\Mocks\MockWebhookConfigRepository;
use Adyen\Core\Tests\BusinessLogic\Domain\Webhook\Mocks\MockWebhookProxy;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

/**
 * Class ValidationServiceTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\InfoSettings\Services
 */
class ValidationServiceTest extends BaseTestCase
{
    /**
     * @var ValidationService
     */
    private $service;

    /**
     * @var MockWebhookProxy
     */
    private $proxy;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(
            WebhookConfigRepository::class,
            static function () {
                return new MockWebhookConfigRepository();
            }
        );

        $this->proxy = new MockWebhookProxy();

        TestServiceRegister::registerService(
            WebhookProxy::class,
            function () {
                return $this->proxy;
            }
        );

        $this->service = TestServiceRegister::getService(ValidationService::class);
    }

    /**
     * @throws Exception
     */
    public function testWebhookTestFails(): void
    {
        // arrange

        // act
        $result = $this->service->validateWebhook();

        // assert
        self::assertFalse($result);
    }

    /**
     * @throws Exception
     */
    public function testWebhookTestSuccess(): void
    {
        // arrange
        $this->proxy->setMockTestResponse(
            file_get_contents(__DIR__ . '/../../../Common/ApiResponses/Webhook/testWebhook.json')
        );

        // act
        $result = $this->service->validateWebhook();

        // assert
        self::assertTrue($result);
    }

    /**
     * @throws Exception
     */
    public function testValidationReportSuccess(): void
    {
        // arrange
        $this->proxy->setMockTestResponse(
            file_get_contents(__DIR__ . '/../../../Common/ApiResponses/Webhook/testWebhookFail.json')
        );

        // act
        $result = $this->service->validationReport();

        // assert
        self::assertNotEmpty($result);
        self::assertStringEqualsFile(
            __DIR__ . '/../../../Common/ApiResponses/Webhook/testWebhookFail.json',
            $result
        );
    }

    /**
     * @throws Exception
     */
    public function testValidationReportFail(): void
    {
        // arrange
        $this->proxy->setMockTestResponse(
            file_get_contents(__DIR__ . '/../../../Common/ApiResponses/Webhook/testWebhook.json')
        );

        // act
        $result = $this->service->validationReport();

        // assert
        self::assertNotEmpty($result);
        self::assertStringEqualsFile(
            __DIR__ . '/../../../Common/ApiResponses/Webhook/testWebhook.json',
            $result
        );
    }
}
