<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\Merchant\MockComponents;

use Adyen\Core\BusinessLogic\AdyenAPI\Management\Merchant\Http\Proxy;
use Adyen\Core\BusinessLogic\Domain\Merchant\Models\Merchant;
use Exception;

class MockMerchantProxy extends Proxy
{
    public $getMerchantFails = false;
    public $generateClientKeyFails = false;

    public function getMerchantById(string $merchantId): ?Merchant
    {
        if ($this->getMerchantFails) {
            throw new Exception('Failed getting merchant by id.');
        }

        return new Merchant('1234', 'Merchant Name', '', '');
    }

    public function generateClientKey(): string
    {
        if ($this->generateClientKeyFails) {
            throw new Exception('Failed client key generation.');
        }

        return '0123456789';
    }
}
