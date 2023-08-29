<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Payment\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethodResponse as PaymentMethodResponseModel;

/**
 * Class AvailableMethodsResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Payment\Response
 */
class AvailableMethodsResponse extends Response
{
    /**
     * @var PaymentMethodResponseModel[]
     */
    private $methodResponses;

    /**
     * @param PaymentMethodResponseModel[] $methodResponses
     */
    public function __construct(array $methodResponses)
    {
        $this->methodResponses = $methodResponses;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $methodResponsesArray = [];

        foreach ($this->methodResponses as $methodResponse) {
            $methodResponsesArray[] = $this->transformMethodResponse($methodResponse);
        }

        return $methodResponsesArray;
    }

    /**
     * @param PaymentMethodResponseModel $methodResponse
     *
     * @return array
     */
    private function transformMethodResponse(PaymentMethodResponseModel $methodResponse): array
    {
        return [
            'methodId' => $methodResponse->getMethodId(),
            'code' => $methodResponse->getCode(),
            'status' => $methodResponse->isEnabled(),
            'countries' => $methodResponse->getCountries(),
            'currencies' => $methodResponse->getCurrencies(),
            'logo' => $methodResponse->getLogo(),
            'paymentType' => $methodResponse->getType(),
            'name' => $methodResponse->getName(),
        ];
    }
}
