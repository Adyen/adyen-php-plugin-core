<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response\WebhookReportResponse;
use Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response\WebhookValidationResponse;
use Adyen\Core\BusinessLogic\Domain\InfoSettings\Services\ValidationService;
use Exception;

/**
 * Class WebhookValidationController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Controller
 */
class WebhookValidationController
{
    /**
     * @var ValidationService
     */
    private $validationService;

    /**
     * @param ValidationService $validationService
     */
    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * @return WebhookValidationResponse
     *
     * @throws Exception
     */
    public function validate(): WebhookValidationResponse
    {
        return new WebhookValidationResponse($this->validationService->validateWebhook());
    }

    /**
     * @return WebhookReportResponse
     *
     * @throws Exception
     */
    public function report(): WebhookReportResponse
    {
        return new WebhookReportResponse($this->validationService->validationReport());
    }
}
