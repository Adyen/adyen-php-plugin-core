<?php

namespace Adyen\Core\BusinessLogic\Domain\PartialPayments\Service;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\BalanceRequest;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\BalanceResult;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\OrderCreateRequest;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\OrderCreateResult;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Proxies\PartialPaymentProxy;

/**
 * Class PartialPaymentService
 *
 * @package Adyen\Core\BusinessLogic\Domain\PartialPayments\Service
 */
class PartialPaymentService
{
    /**
     * @var PartialPaymentProxy
     */
    private $proxy;
    /**
     * @var ConnectionService
     */
    private $connectionService;

    /**
     * @param PartialPaymentProxy $proxy
     * @param ConnectionService $connectionService
     */
    public function __construct(PartialPaymentProxy $proxy, ConnectionService $connectionService)
    {
        $this->proxy = $proxy;
        $this->connectionService = $connectionService;
    }

    /**
     * @param Amount $amount
     * @param array $paymentMethod
     *
     * @return BalanceResult
     */
    public function checkBalance(Amount $amount, array $paymentMethod): BalanceResult
    {
        $connectionSettings = $this->connectionService->getConnectionData();
        $merchantAccount = $connectionSettings ? $connectionSettings->getActiveConnectionData()->getMerchantId() : '';

        return $this->proxy->getBalance(new BalanceRequest($paymentMethod, $merchantAccount, $amount));
    }

    /**
     * @param $reference
     * @param Amount $amount
     *
     * @return OrderCreateResult
     */
    public function createOrder($reference, Amount $amount): OrderCreateResult
    {
        $connectionSettings = $this->connectionService->getConnectionData();
        $merchantAccount = $connectionSettings ? $connectionSettings->getActiveConnectionData()->getMerchantId() : '';

        return $this->proxy->createOrder(new OrderCreateRequest($reference, $merchantAccount, $amount));
    }
}
