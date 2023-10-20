<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\PaymentLink\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLink;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Proxies\PaymentLinkProxy;

/**
 * Class MockPaymentLinkProxy
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\PaymentLink\MockComponents
 */
class MockPaymentLinkProxy implements PaymentLinkProxy
{
    /**
     * @var PaymentLink
     */
    private $paymentLink;

    /**
     * @var true
     */
    private $isCalled = false;

    /**
     * @var PaymentLinkRequest
     */
    private $lastRequest = null;

    public function __construct()
    {
        $this->paymentLink = new PaymentLink('test.com/id', '2023-11-06T00:00:00Z');
    }

    /**
     * @inheritDoc
     */
    public function createPaymentLink(PaymentLinkRequest $request): PaymentLink
    {
        $this->isCalled = true;
        $this->lastRequest = $request;

        return $this->paymentLink;
    }

    /**
     * @param PaymentLink $link
     *
     * @return void
     */
    public function setPaymentLink(PaymentLink $link): void
    {
        $this->paymentLink = $link;
    }

    /**
     * @return PaymentLink
     */
    public function getPaymentLink(): PaymentLink
    {
        return $this->paymentLink;
    }

    /**
     * @return bool
     */
    public function getIsCalled(): bool
    {
        return $this->isCalled;
    }
    /**
     * @return PaymentLinkRequest|null
     */
    public function getLastRequest(): ?PaymentLinkRequest
    {
        return $this->lastRequest;
    }
}
