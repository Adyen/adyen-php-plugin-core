<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\Cancel\Mocks;

use Adyen\Core\BusinessLogic\Domain\Cancel\Models\CancelRequest;
use Adyen\Core\BusinessLogic\Domain\Cancel\Proxies\CancelProxy;

/**
 * Class MockCancelProxy
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\Cancel\Mocks
 */
class MockCancelProxy implements CancelProxy
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
     * @param bool $success
     *
     * @return void
     */
    public function setMockSuccess(bool $success): void
    {
        $this->success = $success;
    }

    /**
     * @inheritDoc
     */
    public function cancelPayment(CancelRequest $request): bool
    {
        return $this->success;
    }
}
