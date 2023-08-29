<?php

namespace Adyen\Core\BusinessLogic\Domain\Disconnect\Repositories;

use DateTime;
use Exception;

/**
 * Class DisconnectRepository
 *
 * @package Adyen\Core\BusinessLogic\Domain\Disconnect\Repositories
 */
interface DisconnectRepository
{
    /**
     * Retrieves disconnect time.
     *
     * @return DateTime|null
     *
     * @throws Exception
     */
    public function getDisconnectTime(): ?DateTime;

    /**
     * Sets disconnect time.
     *
     * @param DateTime $disconnectTime
     *
     * @return void
     *
     * @throws Exception
     */
    public function setDisconnectTime(DateTime $disconnectTime): void;

    /**
     * Deletes disconnect time.
     *
     * @return void
     *
     * @throws Exception
     */
    public function deleteDisconnectTime(): void;
}
