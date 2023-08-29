<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Merchant\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\Domain\Merchant\Models\Merchant;

/**
 * Class MerchantResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Merchant\Response
 */
class MerchantResponse extends Response
{
    /**
     * @var Merchant[]
     */
    private $merchants;

    /**
     * @param Merchant[] $merchants
     */
    public function __construct(array $merchants)
    {
        $this->merchants = $merchants;
    }

    /**
     *  Transforms merchants to array.
     *
     * @return array Array representation of merchants.
     */
    public function toArray(): array
    {
        $merchantArray = [];

        foreach ($this->merchants as $merchant) {
            $merchantArray[] = $this->transformMerchant($merchant);
        }

        return $merchantArray;
    }

    /**
     * @param Merchant $merchant
     *
     * @return array
     */
    private function transformMerchant(Merchant $merchant): array
    {
        return [
            'merchantName' => $merchant->getMerchantName(),
            'merchantId' => $merchant->getMerchantId()
        ];
    }
}
