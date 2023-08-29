<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Capture\Requests;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\Domain\Capture\Models\CaptureRequest;

/**
 * Class CaptureHttpRequest
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Capture\Requests
 */
class CaptureHttpRequest extends HttpRequest
{
    /**
     * @var CaptureRequest
     */
    private $captureRequest;

    /**
     * @param CaptureRequest $captureRequest
     */
    public function __construct(CaptureRequest $captureRequest)
    {
        $this->captureRequest = $captureRequest;

        parent::__construct('/payments/' . $captureRequest->getPspReference() . '/captures', $this->transformBody());
    }

    /**
     * @return array
     */
    private function transformBody(): array
    {
        return [
            'merchantAccount' => $this->captureRequest->getMerchantAccount(),
            'amount' => [
                'currency' => $this->captureRequest->getAmount()->getCurrency()->getIsoCode(),
                'value' => $this->captureRequest->getAmount()->getValue(),
            ]
        ];
    }
}
