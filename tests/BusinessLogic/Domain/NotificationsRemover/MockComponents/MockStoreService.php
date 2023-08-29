<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\NotificationsRemover\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Stores\Models\Store;
use Adyen\Core\BusinessLogic\Domain\Stores\Services\StoreService;

class MockStoreService extends StoreService
{
    public $methodCalled = false;

    public function getStores(): array
    {
        $this->methodCalled = true;

        return [
            new Store('store1', 'Store 1', true),
            new Store('store2', 'Store 2', false),
            new Store('store3', 'Store 3', false),
        ];
    }
}
