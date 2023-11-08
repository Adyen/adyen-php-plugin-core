<?php

namespace Adyen\Core\BusinessLogic\E2ETest\Services;

/**
 * Class CreateSeedDataService
 *
 * @package Adyen\Core\BusinessLogic\E2ETest\Services
 */
abstract class CreateSeedDataService
{
    /**
     * Creates initial data
     *
     * @return void
     */
    public function createInitialData(): void
    {
        $this->updateDefaultShopName();
        $this->createSubStores();
    }

    /**
     * @return void
     */
    abstract public function updateDefaultShopName(): void;

    /**
     * @return void
     */
    abstract public function createSubStores(): void;
}