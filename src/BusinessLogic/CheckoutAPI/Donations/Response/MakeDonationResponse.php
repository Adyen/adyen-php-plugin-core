<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\Donations\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class MakeDonationResponse
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\Donations\Response
 */
class MakeDonationResponse extends Response
{
    /**
     * @var string
     */
    private $status;

    /**
     * @param string $status
     */
    public function __construct(string $status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
        ];
    }

    public function isSuccessful(): bool
    {
        return in_array($this->status, ['completed', 'pending']);
    }
}
