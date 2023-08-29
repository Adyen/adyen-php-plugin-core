<?php

namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Merchant\Models\Merchant;
use Adyen\Core\BusinessLogic\Domain\Merchant\Proxies\MerchantProxy;

class MockMerchantProxy implements MerchantProxy
{
    /**
     * @var \Adyen\Core\BusinessLogic\Domain\Merchant\Models\Merchant[]
     */
    private $merchants;

    /**
     * @inheritDoc
     */
    public function getMerchants(): array
    {
        return $this->merchants;
    }

    /**
     * @param Merchant[] $merchants
     *
     * @return void
     */
    public function setMockResult(array $merchants): void
    {
        $this->merchants = $merchants;
    }

    public function getMerchantById(string $merchantId): ?Merchant
    {
        return null;
    }

    public function generateClientKey(): string
    {
        return '';
    }
}
