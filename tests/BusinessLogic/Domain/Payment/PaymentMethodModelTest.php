<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\Payment;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidPaymentMethodCodeException;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\DuplicatedValuesNotAllowedException;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\InvalidCardConfigurationException;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\NegativeValuesNotAllowedException;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\PaymentMethodDataEmptyException;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\StringValuesNotAllowedException;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\AuthorizationType;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\Exceptions\InvalidAuthorizationTypeException;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\Exceptions\InvalidTokenTypeException;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\CardConfig;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\TokenType;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class PaymentMethodModelTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\Payment
 */
class PaymentMethodModelTest extends BaseTestCase
{
    public function testTotalSurchargeCalculationForNoneType()
    {
        $paymentMethod = new PaymentMethod('test123', 'scheme', 'Test');
        $paymentMethod->setSurchargeType('none');

        self::assertEquals(0, $paymentMethod->getTotalSurchargeFor(123.45));
    }

    public function testTotalSurchargeCalculationForCombinedType()
    {
        $paymentMethod = new PaymentMethod('test123', 'scheme', 'Test');
        $paymentMethod->setSurchargeType('combined');
        $paymentMethod->setFixedSurcharge(100);
        $paymentMethod->setPercentSurcharge(10);

        self::assertEquals(110, $paymentMethod->getTotalSurchargeFor(100));
    }

    public function testTotalSurchargeCalculationLimit()
    {
        $paymentMethod = new PaymentMethod('test123', 'scheme', 'Test');
        $paymentMethod->setSurchargeType('combined');
        $paymentMethod->setFixedSurcharge(100);
        $paymentMethod->setPercentSurcharge(10);
        $paymentMethod->setSurchargeLimit(5);

        self::assertEquals(105, $paymentMethod->getTotalSurchargeFor(100));
    }

    /**
     * @throws InvalidPaymentMethodCodeException
     * @throws PaymentMethodDataEmptyException
     */
    public function testTokenTypeException()
    {
        // arrange
        $this->expectException(InvalidTokenTypeException::class);
        new PaymentMethod(
            'test123',
            'scheme',
            'Test',
            '',
            '',
            [],
            [],
            '',
            '',
            '',
            '',
            null,
            '',
            '',
            null,
            false,
            true,
            TokenType::fromState('test')
        );
    }

    /**
     * @return void
     *
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidTokenTypeException
     * @throws InvalidPaymentMethodCodeException
     * @throws DuplicatedValuesNotAllowedException
     * @throws InvalidCardConfigurationException
     * @throws NegativeValuesNotAllowedException
     * @throws PaymentMethodDataEmptyException
     * @throws StringValuesNotAllowedException
     */
    public function testAuthorizationTypeException()
    {
        // arrange
        $this->expectException(InvalidAuthorizationTypeException::class);
        new PaymentMethod(
            'test123',
            'scheme',
            'Test',
            '',
            '',
            [],
            [],
            '',
            '',
            '',
            '',
            null,
            '',
            '',
            new CardConfig(false,
                false,
                false,
                false,
                false,
                [],
                0,
                []),
            false,
            true,
            TokenType::fromState('CardOnFile'),
            AuthorizationType::fromState('testFail')
        );
    }

    /**
     * @return void
     *
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidTokenTypeException
     * @throws InvalidPaymentMethodCodeException
     * @throws DuplicatedValuesNotAllowedException
     * @throws InvalidCardConfigurationException
     * @throws NegativeValuesNotAllowedException
     * @throws PaymentMethodDataEmptyException
     * @throws StringValuesNotAllowedException
     */
    public function testFinalAuthorizationType(): void
    {
        // arrange
        $method = new PaymentMethod(
            'test123',
            'scheme',
            'Test',
            '',
            '',
            [],
            [],
            '',
            '',
            '',
            '',
            null,
            0,
            '',
            new CardConfig(false,
                false,
                false,
                false,
                false,
                [],
                0,
                []),
            false,
            true,
            TokenType::fromState('CardOnFile'),
            AuthorizationType::fromState('FinalAuth')
        );

        self::assertEquals(AuthorizationType::finalAuthorization(), $method->getAuthorizationType());
        self::assertEquals(AuthorizationType::finalAuthorization()->getType(), $method->getAuthorizationType()->getType());
    }

    /**
     * @return void
     *
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidTokenTypeException
     * @throws InvalidPaymentMethodCodeException
     * @throws DuplicatedValuesNotAllowedException
     * @throws InvalidCardConfigurationException
     * @throws NegativeValuesNotAllowedException
     * @throws PaymentMethodDataEmptyException
     * @throws StringValuesNotAllowedException
     */
    public function testPreAuthorizationType(): void
    {
        // arrange
        $method = new PaymentMethod(
            'test123',
            'scheme',
            'Test',
            '',
            '',
            [],
            [],
            '',
            '',
            '',
            '',
            null,
            0,
            '',
            new CardConfig(false,
                false,
                false,
                false,
                false,
                [],
                0,
                []),
            false,
            true,
            TokenType::fromState('CardOnFile'),
            AuthorizationType::fromState('PreAuth')
        );

        self::assertEquals(AuthorizationType::preAuthorization(), $method->getAuthorizationType());
        self::assertEquals(AuthorizationType::preAuthorization()->getType(), $method->getAuthorizationType()->getType());
    }
}
