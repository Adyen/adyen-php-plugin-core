<?php

namespace Adyen\Core\BusinessLogic\Domain\PartialPayments\Models;

/**
 * Class OrderCancelResult
 *
 * @package Adyen\Core\BusinessLogic\Domain\PartialPayments\Models
 */
class OrderCancelResult
{
    /**
     * @var string
     */
    private $pspReference;
    /**
     * @var string
     */
    private $resultCode;

    /**
     * @param string $pspReference
     * @param string $resultCode
     */
    public function __construct(string $pspReference, string $resultCode)
    {
        $this->pspReference = $pspReference;
        $this->resultCode = $resultCode;
    }

    public function getPspReference(): string
    {
        return $this->pspReference;
    }

    public function getResultCode(): string
    {
        return $this->resultCode;
    }
}
