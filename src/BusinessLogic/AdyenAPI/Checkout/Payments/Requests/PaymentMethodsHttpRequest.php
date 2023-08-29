<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Payments\Requests;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodsRequest;

/**
 * Class PaymentMethodsHttpRequest
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Payments\Requests
 */
class PaymentMethodsHttpRequest extends HttpRequest
{
    /**
     * @var PaymentMethodsRequest
     */
    private $request;

    public function __construct(PaymentMethodsRequest $request)
    {
        $this->request = $request;

        parent::__construct('/paymentMethods', $this->transformBody());
    }

    /**
     * Transforms webhook request to array.
     *
     * @return array
     */
    public function transformBody(): array
    {
        $requestBody = [
            'merchantAccount' => $this->request->getMerchantId(),
            'allowedPaymentMethods' => $this->request->getAllowedPaymentMethods(),
        ];

        if ($this->request->getAmount()) {
            $requestBody['amount'] = [
                'value' => $this->request->getAmount()->getValue(),
                'currency' => $this->request->getAmount()->getCurrency()->getIsoCode(),
            ];
        }

        if ($this->request->getCountry()) {
            $requestBody['countryCode'] = $this->request->getCountry()->getIsoCode();
        }

        if ($this->request->getShopperLocale()) {
            $requestBody['shopperLocale'] = $this->request->getShopperLocale();
        }

        if ($this->request->getShopperReference()) {
            $requestBody['shopperReference'] = (string)$this->request->getShopperReference();
        }

        return $requestBody;
    }
}
