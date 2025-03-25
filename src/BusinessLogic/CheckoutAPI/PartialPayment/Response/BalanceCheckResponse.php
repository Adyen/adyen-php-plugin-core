<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\PartialPayment\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\BalanceResult;

/**
 * Class BalanceCheckResponse
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\PartialPayment\Response
 */
class BalanceCheckResponse extends Response
{
    /**
     * @var BalanceResult
     */
    private $result;

    /**
     * @param BalanceResult $result
     */
    public function __construct(BalanceResult $result)
    {
        $this->result = $result;
    }

    public function toArray(): array
    {
        $balance = $this->result->getBalance();

        return [
            'pspReference' => $this->result->getPspReference(),
            'resultCode' => $this->result->getResultCode(),
            'balance' => $balance ? [
                'currency' => $balance->getCurrency()->getIsoCode(),
                'value' => $balance->getValue(),
            ] : null,
        ];
    }
}