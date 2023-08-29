<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Connection\Request;

use Adyen\Core\BusinessLogic\AdminAPI\Request\Request;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\EmptyConnectionDataException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\EmptyStoreException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;

/**
 * Class ConnectionRequest
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Connection\Request
 */
class ConnectionRequest extends Request
{
    /**
     * @var string
     */
    private $storeId;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var ?string
     */
    private $testApiKey;

    /**
     * @var ?string
     */
    private $testMerchantId;

    /**
     * @var ?string
     */
    private $liveApiKey;

    /**
     * @var ?string
     */
    private $liveMerchantId;

    /**
     * @param string $storeId
     * @param string $mode
     * @param string|null $testApiKey
     * @param string|null $testMerchantId
     * @param string|null $liveApiKey
     * @param string|null $liveMerchantId
     */
    public function __construct(
        string $storeId,
        string $mode,
        ?string $testApiKey,
        ?string $testMerchantId,
        ?string $liveApiKey,
        ?string $liveMerchantId
    ) {
        $this->storeId = $storeId;
        $this->mode = $mode;
        $this->testApiKey = $testApiKey;
        $this->testMerchantId = $testMerchantId;
        $this->liveMerchantId = $liveMerchantId;
        $this->liveApiKey = $liveApiKey;
    }

    /**
     * @return ConnectionSettings
     *
     * @throws EmptyConnectionDataException
     * @throws EmptyStoreException
     * @throws InvalidModeException
     */
    public function transformToDomainModel(): object
    {
        return new ConnectionSettings(
            $this->storeId,
            $this->mode,
            $this->testApiKey ? new ConnectionData(
                trim($this->testApiKey), $this->testMerchantId
            ) : null,
            $this->liveApiKey ? new ConnectionData(
                trim($this->liveApiKey), $this->liveMerchantId
            ) : null
        );
    }
}
