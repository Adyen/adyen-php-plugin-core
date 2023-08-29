<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Cancel\Request;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\Domain\Cancel\Models\CancelRequest;

/**
 * Class CancelHttpRequest
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Cancel\Request
 */
class CancelHttpRequest extends HttpRequest
{
    /**
     * @var CancelRequest
     */
    private $cancelRequest;

    /**
     * @param CancelRequest $cancelRequest
     */
    public function __construct(CancelRequest $cancelRequest)
    {
        $this->cancelRequest = $cancelRequest;

        parent::__construct('/payments/' . $cancelRequest->getPspReference() . '/cancels', $this->transformBody());
    }

    /**
     * @return array
     */
    private function transformBody(): array
    {
        return [
            'merchantAccount' => $this->cancelRequest->getMerchantAccount(),
            'reference' => $this->cancelRequest->getMerchantReference()
        ];
    }
}
