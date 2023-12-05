<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\PaymentLink\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\PaymentLink\Request\CreatePaymentLinkRequest;
use Adyen\Core\BusinessLogic\AdminAPI\PaymentLink\Response\CreatePaymentLinkResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Services\PaymentLinkService;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;

/**
 * Class PaymentLinkController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\PaymentLink\Controller
 */
class PaymentLinkController
{
    /**
     * @var PaymentLinkService
     */
    protected $paymentLinkService;

    /**
     * @param PaymentLinkService $paymentLinkService
     */
    public function __construct(PaymentLinkService $paymentLinkService)
    {
        $this->paymentLinkService = $paymentLinkService;
    }

    /**
     * @param CreatePaymentLinkRequest $request
     *
     * @return CreatePaymentLinkResponse
     *
     * @throws InvalidMerchantReferenceException
     * @throws InvalidCurrencyCode
     */
    public function createPaymentLink(CreatePaymentLinkRequest $request): CreatePaymentLinkResponse
    {
        $link = $this->paymentLinkService->createPaymentLink($request->transformToDomainModel());

        return new CreatePaymentLinkResponse($link->getUrl());
    }
}
