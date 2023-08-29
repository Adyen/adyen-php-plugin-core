<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\Refund\Mocks;

use Adyen\Core\BusinessLogic\Domain\Refund\Models\RefundRequest;
use Adyen\Core\BusinessLogic\Domain\Refund\Proxies\RefundProxy;

/**
 * Class MockRefundProxy
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\Refund\Mocks
 */
class MockRefundProxy implements RefundProxy
{
    /**
     * @var bool
     */
    private $success;

    public function __construct()
    {
        $this->success = true;
    }

    /**
     * @inheritDoc
     */
    public function refundPayment(RefundRequest $request): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     *
     * @return void
     */
    public function setMockSuccess(bool $success): void
    {
        $this->success = $success;
    }
}
