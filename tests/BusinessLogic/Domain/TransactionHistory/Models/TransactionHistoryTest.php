<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\TransactionHistory\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\CurrencyMismatchException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class TransactionHistoryTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\TransactionHistory\Models
 */
class TransactionHistoryTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testAddingFirstHistoryItem(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory('merchantRef', CaptureType::manual());
        $item = new HistoryItem(
            'pspRef',
            'merchantRef',
            'CODE',
            'paymentState',
            'date',
            true,
            Amount::fromInt(1, Currency::getDefault()),
            'paymentMethod',
            0,
            false
        );
        // act
        $transactionHistory->add($item);

        // assert
        self::assertEquals('pspRef', $transactionHistory->getOriginalPspReference());
        self::assertEquals('merchantRef', $transactionHistory->getMerchantReference());
        self::assertEquals('paymentMethod', $transactionHistory->getPaymentMethod());
        self::assertFalse($transactionHistory->collection()->isEmpty());
        self::assertCount(1, $transactionHistory->collection()->getAll());
    }

    /**
     * @return void
     */
    public function testAddingHistoryItems(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory('merchantRef', CaptureType::manual());
        $item1 = new HistoryItem(
            'pspRef',
            'merchantRef',
            'CODE',
            'paymentState',
            'date',
            true,
            Amount::fromInt(1, Currency::getDefault()),
            'paymentMethod',
            0,
            false
        );
        $item2 = new HistoryItem(
            'pspRef2',
            'merchantRef',
            'CODE2',
            'paymentState2',
            'date2',
            true,
            Amount::fromInt(1, Currency::getDefault()),
            'paymentMethod2',
            0,
            false
        );
        $item3 = new HistoryItem(
            'pspRef3',
            'merchantRef',
            'CODE3',
            'paymentState3',
            'date3',
            true,
            Amount::fromInt(1, Currency::getDefault()),
            'paymentMethod3',
            0,
            false
        );
        // act
        $transactionHistory->add($item1);
        $transactionHistory->add($item2);
        $transactionHistory->add($item3);

        // assert
        self::assertEquals('pspRef', $transactionHistory->getOriginalPspReference());
        self::assertEquals('merchantRef', $transactionHistory->getMerchantReference());
        self::assertEquals('paymentMethod', $transactionHistory->getPaymentMethod());
        self::assertFalse($transactionHistory->collection()->isEmpty());
        self::assertCount(3, $transactionHistory->collection()->getAll());
        self::assertEquals($item3, $transactionHistory->collection()->last());
    }

    /**
     * @return void
     */
    public function testFiltering(): void
    {
        // arrange
        $transactionHistory = $this->transactionHistory();
        // act
        self::assertCount(1, $transactionHistory->collection()->filterByPspReference('pspRef1')->getAll());
        self::assertCount(5, $transactionHistory->collection()->filterByEventCode('CODE1')->getAll());
        self::assertCount(6, $transactionHistory->collection()->filterByStatus(true)->getAll());
    }

    /**
     * @return void
     *
     * @throws CurrencyMismatchException
     */
    public function testGetItemByPspReference(): void
    {
        // arrange
        $transactionHistory = $this->transactionHistory();

        // act
        $result = $transactionHistory->collection()->filterByPspReference('pspRef1')->first();

        // assert
        self::assertEquals(
            $result,
            new HistoryItem(
                'pspRef1',
                'merchantRef',
                'CODE1',
                'paymentState',
                'date',
                true,
                Amount::fromInt(1, Currency::getDefault()),
                'paymentMethod',
                0,
                false
            )
        );
    }

    /**
     * @return void
     *
     * @throws CurrencyMismatchException
     */
    public function testTotalAmount(): void
    {
        // arrange
        $transactionHistory = $this->transactionHistory();

        // act
        $result = $transactionHistory->collection()->getTotalAmount(Currency::getDefault());

        // assert
        self::assertEquals($result, Amount::fromInt(10, Currency::getDefault()));
    }

    /**
     * @return void
     *
     * @throws CurrencyMismatchException
     */
    public function testTotalAmountByEventCode(): void
    {
        // arrange
        $transactionHistory = $this->transactionHistory();

        // act
        $result = $transactionHistory->getTotalAmountForEventCode('CODE1');

        // assert
        self::assertEquals($result, Amount::fromInt(2, Currency::getDefault()));
    }

    public function testGetCaptureAmount(): void
    {
        // arrange
        $transactionHistory = new TransactionHistory(
            'merchantRef', CaptureType::manual(), 0, Currency::getDefault(),
            [
                new HistoryItem(
                    'originalPsp',
                    'merchantRef',
                    'AUTHORISATION',
                    'paymentState',
                    'date',
                    true,
                    Amount::fromInt(100, Currency::getDefault()),
                    'paymentMethod',
                    0,
                    false
                ),
                new HistoryItem(
                    'pspRef1',
                    'merchantRef',
                    'CAPTURE',
                    'paymentState',
                    'date',
                    true,
                    Amount::fromInt(1, Currency::getDefault()),
                    'paymentMethod',
                    0,
                    false
                ),
                new HistoryItem(
                    'pspRef2',
                    'merchantRef',
                    'CAPTURE',
                    'paymentState',
                    'date',
                    true,
                    Amount::fromInt(1, Currency::getDefault()),
                    'paymentMethod',
                    0,
                    false
                ),
            ]
        );

        // act
        $result = $transactionHistory->getCapturedAmount();

        // assert
        self::assertEquals($result, Amount::fromInt(2, Currency::getDefault()));
    }

    /**
     * @return TransactionHistory
     */
    private function transactionHistory(): TransactionHistory
    {
        return new TransactionHistory('merchantRef', CaptureType::manual(), 0, Currency::getDefault(), [
            new HistoryItem(
                'originalPsp',
                'merchantRef',
                'CODE1',
                'paymentState',
                'date',
                true,
                Amount::fromInt(1, Currency::getDefault()),
                'paymentMethod',
                0,
                false
            ),
            new HistoryItem(
                'pspRef1',
                'merchantRef',
                'CODE1',
                'paymentState',
                'date',
                true,
                Amount::fromInt(1, Currency::getDefault()),
                'paymentMethod',
                0,
                false
            ),
            new HistoryItem(
                'pspRef2',
                'merchantRef',
                'CODE1',
                'paymentState',
                'date',
                false,
                Amount::fromInt(1, Currency::getDefault()),
                'paymentMethod',
                0,
                false
            ),
            new HistoryItem(
                'pspRef3',
                'merchantRef',
                'CODE2',
                'paymentState',
                'date',
                true,
                Amount::fromInt(1, Currency::getDefault()),
                'paymentMethod',
                0,
                false
            ),
            new HistoryItem(
                'pspRef4',
                'merchantRef',
                'CODE2',
                'paymentState',
                'date',
                true,
                Amount::fromInt(1, Currency::getDefault()),
                'paymentMethod',
                0,
                false
            ),
            new HistoryItem(
                'pspRef5',
                'merchantRef',
                'CODE1',
                'paymentState',
                'date',
                false,
                Amount::fromInt(1, Currency::getDefault()),
                'paymentMethod3',
                0,
                false
            ),
            new HistoryItem(
                'pspRef6',
                'merchantRef',
                'CODE1',
                'paymentState2',
                'date',
                false,
                Amount::fromInt(1, Currency::getDefault()),
                'paymentMethod',
                0,
                false
            ),
            new HistoryItem(
                'pspRef7',
                'merchantRef',
                'CODE3',
                'paymentState',
                'date',
                false,
                Amount::fromInt(1, Currency::getDefault()),
                'paymentMethod',
                0,
                false
            ),
            new HistoryItem(
                'pspRef8',
                'merchantRef',
                'CODE3',
                'paymentState',
                'date',
                true,
                Amount::fromInt(1, Currency::getDefault()),
                'paymentMethod',
                0,
                false
            ),
            new HistoryItem(
                'pspRef9',
                'merchantRef',
                'CODE6',
                'paymentState',
                'date',
                true,
                Amount::fromInt(1, Currency::getDefault()),
                'paymentMethod',
                0,
                false
            )
        ]);
    }
}
