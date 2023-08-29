<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Repositories;

use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models\DonationsData;
use Exception;

/**
 * Class DonationsDataRepository
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Repositories
 */
interface DonationsDataRepository
{
    /**
     * Saves donations data.
     *
     * @param DonationsData $data
     *
     * @return void
     *
     * @throws Exception
     */
    public function saveDonationsData(DonationsData $data): void;

    /**
     * Retrieves donations data.
     *
     * @param string $merchantReference
     *
     * @return DonationsData|null
     *
     * @throws Exception
     */
    public function getDonationsData(string $merchantReference): ?DonationsData;

    /**
     * Deletes donations data by merchant reference.
     *
     * @param string $merchantReference
     *
     * @return void
     *
     * @throws Exception
     */
    public function deleteDonationsData(string $merchantReference): void;

    /**
     * Deletes all donations data.
     *
     * @param int $limit
     *
     * @return void
     *
     * @throws Exception
     */
    public function delete(int $limit = 5000): void;

    /**
     * Checks if there are donations data records in the database.
     *
     * @return bool
     *
     * @throws Exception
     */
    public function donationDataExists(): bool;
}
