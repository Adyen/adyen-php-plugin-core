<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\Store\Models;

use Adyen\Core\BusinessLogic\Domain\Stores\Exceptions\InvalidShopOrderDataException;
use Adyen\Core\BusinessLogic\Domain\Stores\Models\StoreOrderStatus;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Exception;

/**
 * Class StoreOrderStatusModelTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\Store\Models
 */
class StoreOrderStatusModelTest extends BaseTestCase
{
    /**
     * @throws Exception
     */
    public function testInvalidShopOrderDataExceptionIdMissing(): void
    {
        // arrange
        $this->expectException(InvalidShopOrderDataException::class);

        // act
        new StoreOrderStatus('', 'name');
        // assert
    }

    /**
     * @throws Exception
     */
    public function testInvalidShopOrderDataExceptionNameMissing(): void
    {
        // arrange
        $this->expectException(InvalidShopOrderDataException::class);

        // act
        new StoreOrderStatus('1', '');
        // assert
    }
}
