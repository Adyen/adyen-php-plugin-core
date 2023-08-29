<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\InfoSettings\Mocks;

use Adyen\Core\BusinessLogic\Domain\InfoSettings\Services\ValidationService;

/**
 * Class MockValidationService
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\InfoSettings\Mocks
 */
class MockValidationService extends ValidationService
{
    private $success = false;

    public function validateWebhook(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     *
     * @return void
     */
    public function setMockValidationSuccess(bool $success): void
    {
        $this->success = $success;
    }

    /**
     * @return string
     */
    public function validationReport(): string
    {
        return 'REPORT';
    }
}
