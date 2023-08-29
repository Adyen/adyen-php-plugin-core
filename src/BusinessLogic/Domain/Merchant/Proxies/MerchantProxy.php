<?php

namespace Adyen\Core\BusinessLogic\Domain\Merchant\Proxies;

use Adyen\Core\BusinessLogic\Domain\Merchant\Models\Merchant;
use Exception;

/**
 * Class MerchantProxy
 *
 * @package Adyen\Core\BusinessLogic\Domain\Merchant\Proxies
 */
interface MerchantProxy
{
    /**
     * Get all merchants.
     *
     * @return Merchant[]
     */
    public function getMerchants(): array;

    /**
     * Retrieves merchant by id.
     *
     * @param string $merchantId
     *
     * @return Merchant|null
     *
     * @throws Exception
     */
    public function getMerchantById(string $merchantId): ?Merchant;

    /**
     * Generates client key.
     *
     * @return string
     *
     * @throws Exception
     */
    public function generateClientKey(): string;
}
