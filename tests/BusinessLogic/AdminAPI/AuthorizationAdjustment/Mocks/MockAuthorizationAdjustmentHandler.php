<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\AuthorizationAdjustment\Mocks;

use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Handlers\AuthorizationAdjustmentHandler;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Exception;

/**
 * Class MockAuthorizationAdjustmentHandler.
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\AuthorizationAdjustment\Mocks
 */
class MockAuthorizationAdjustmentHandler extends AuthorizationAdjustmentHandler
{
    /**
     * @var bool
     */
    private $success = true;

    /**
     * @var Exception
     */
    private $exception = null;

    /**
     * @param string $merchantReference
     *
     * @return bool
     *
     * @throws Exception
     */
    public function handleExtendingAuthorizationPeriod(string $merchantReference): bool
    {
        if ($this->exception) {
            throw  $this->exception;
        }

        return $this->success;
    }

    /**
     * @param string $merchantReference
     * @param Amount $amount
     *
     * @return bool
     *
     * @throws Exception
     */
    public function handleOrderModifications(string $merchantReference, Amount $amount): bool
    {
        if ($this->exception) {
            throw $this->exception;
        }

        return $this->success;
    }

    /**
     * @param bool $success
     *
     * @return void
     */
    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    /**
     * @param Exception $exception
     *
     * @return void
     */
    public function setException(Exception $exception): void
    {
        $this->exception = $exception;
    }
}
