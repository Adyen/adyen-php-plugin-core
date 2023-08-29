<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Merchant\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\Merchant\Response\MerchantResponse;
use Adyen\Core\BusinessLogic\Domain\Merchant\Exceptions\FailedToRetrieveMerchantsException;
use Adyen\Core\BusinessLogic\Domain\Merchant\Services\MerchantService;

/**
 * Class MerchantController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Merchant\Controller
 */
class MerchantController
{
    /**
     * @var MerchantService
     */
    private $merchantService;

    /**
     * @param MerchantService $merchantService
     */
    public function __construct(MerchantService $merchantService)
    {
        $this->merchantService = $merchantService;
    }

    /**
     * @return MerchantResponse
     *
     * @throws FailedToRetrieveMerchantsException
     */
    public function getMerchants(): MerchantResponse
    {
        return new MerchantResponse($this->merchantService->getMerchants());
    }
}
