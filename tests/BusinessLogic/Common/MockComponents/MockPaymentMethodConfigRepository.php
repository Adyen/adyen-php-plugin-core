<?php

namespace Adyen\Core\Tests\BusinessLogic\Common\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Payment\Repositories\PaymentMethodConfigRepository;

/**
 * Class MockPaymentMethodConfigRepository.
 *
 * @package Adyen\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockPaymentMethodConfigRepository implements PaymentMethodConfigRepository
{
    /**
     * @var PaymentMethod[]
     */
    private $enabledExpressCheckoutPaymentMethods = [];

    /**
     * @var bool|null
     */
    private $isGuestArgument;

    /**
     * @inheritDoc
     */
    public function getConfiguredPaymentMethods(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getConfiguredPaymentMethodsForAllShops(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getConfiguredPaymentMethodsEntities(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getEnabledExpressCheckoutPaymentMethods(bool $isGuest): array
    {
        $this->isGuestArgument = $isGuest;

        return $this->enabledExpressCheckoutPaymentMethods;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodById(string $id): ?PaymentMethod
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodByCode(string $code): ?PaymentMethod
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function saveMethodConfiguration(PaymentMethod $method): void
    {
    }

    /**
     * @inheritDoc
     */
    public function updateMethodConfiguration(PaymentMethod $method): void
    {
    }

    /**
     * @inheritDoc
     */
    public function deletePaymentMethodById(string $id): void
    {
    }

    /**
     * @inheritDoc
     */
    public function deleteConfiguredMethods(): void
    {
    }

    /**
     * @param PaymentMethod[] $methods
     *
     * @return void
     */
    public function setEnabledExpressCheckoutPaymentMethods(array $methods): void
    {
        $this->enabledExpressCheckoutPaymentMethods = $methods;
    }

    /**
     * Returns the $isGuest value last passed to getEnabledExpressCheckoutPaymentMethods().
     *
     * @return bool|null
     */
    public function getIsGuestArgument(): ?bool
    {
        return $this->isGuestArgument;
    }
}
