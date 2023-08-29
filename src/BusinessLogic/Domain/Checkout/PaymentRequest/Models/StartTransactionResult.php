<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

/**
 * Class StartTransactionResult
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class StartTransactionResult
{
    /**
     * @var ResultCode
     */
    private $resultCode;
    /**
     * @var string|null
     */
    private $pspReference;
    /**
     * @var mixed|null
     */
    private $action;
    /**
     * @var string
     */
    private $donationToken;

    public function __construct(
        ResultCode $resultCode,
        ?string $pspReference = null,
        $action = null,
        string $donationToken = ''
    )
    {
        $this->resultCode = $resultCode;
        $this->pspReference = $pspReference;
        $this->action = $action;
        $this->donationToken = $donationToken;
    }

    /**
     * @return ResultCode
     */
    public function getResultCode(): ResultCode
    {
        return $this->resultCode;
    }

    public function getPspReference(): ?string
    {
        return $this->pspReference;
    }

    /**
     * @return mixed|null
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getDonationToken(): string
    {
        return $this->donationToken;
    }
}
