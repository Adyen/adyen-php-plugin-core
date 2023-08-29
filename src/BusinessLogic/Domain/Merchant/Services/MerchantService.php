<?php

namespace Adyen\Core\BusinessLogic\Domain\Merchant\Services;

use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\Merchant\Exceptions\ClientKeyGenerationFailedException;
use Adyen\Core\BusinessLogic\Domain\Merchant\Exceptions\ClientPrefixDoesNotExistException;
use Adyen\Core\BusinessLogic\Domain\Merchant\Exceptions\FailedToRetrieveMerchantsException;
use Adyen\Core\BusinessLogic\Domain\Merchant\Models\Merchant;
use Adyen\Core\BusinessLogic\Domain\Merchant\Proxies\MerchantProxy;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Adyen\Core\Infrastructure\Logger\Logger;
use Exception;

/**
 * Class MerchantService
 *
 * @package Adyen\Core\BusinessLogic\Domain\Merchant\Services
 */
class MerchantService
{
    /**
     * @var MerchantProxy
     */
    private $merchantProxy;
    /**
     * @var ConnectionService
     */
    private $connectionService;

    /**
     * @param MerchantProxy $merchantProxy
     * @param ConnectionService $connectionService
     */
    public function __construct(MerchantProxy $merchantProxy, ConnectionService $connectionService)
    {
        $this->merchantProxy = $merchantProxy;
        $this->connectionService = $connectionService;
    }

    /**
     * @return Merchant[]
     *
     * @throws FailedToRetrieveMerchantsException
     */
    public function getMerchants(): array
    {
        try {
            $merchants = $this->merchantProxy->getMerchants();
            $connectionSettings = $this->connectionService->getConnectionData();
            $company = $connectionSettings->getActiveConnectionData()->getApiCredentials()->getCompany();
            $result = [];

            foreach ($merchants as $merchant) {
                if ($merchant->getCompany() === $company) {
                    $result[] = $merchant;
                }
            }

            return $result;
        } catch (Exception $e) {
            Logger::logError($e->getMessage());

            throw new FailedToRetrieveMerchantsException(
                new TranslatableLabel(
                    'Failed to retrieve merchants from Adyen.',
                    'connection.failedToRetrieveMerchants'
                ),
                $e
            );
        }
    }

    /**
     * @return string
     *
     * @throws ClientKeyGenerationFailedException
     * @throws Exception
     */
    public function generateClientKey(): string
    {
        try {
            $key = $this->merchantProxy->generateClientKey();
        } catch (Exception $e) {
            throw new ClientKeyGenerationFailedException(
                new TranslatableLabel(
                    'Failed to generate client key.',
                    'connection.clientKeyGenerationFailed'
                ),
                $e
            );
        }

        return $key;
    }

    /**
     * @param string $merchantId
     *
     * @return string
     *
     * @throws ClientPrefixDoesNotExistException
     */
    public function getLivePrefix(string $merchantId): string
    {
        try {
            $merchant = $this->merchantProxy->getMerchantById($merchantId);

            return $merchant ? $merchant->getClientPrefix() : '';
        } catch (Exception $e) {
            throw new ClientPrefixDoesNotExistException(
                new TranslatableLabel(
                    'Failed to retrieve client prefix.',
                    'connection.noLivePrefix'
                ),
                $e
            );
        }
    }
}
