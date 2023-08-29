<?php

namespace Adyen\Core\Tests\BusinessLogic\DataAccess\Payment\Entities;

use Adyen\Core\BusinessLogic\DataAccess\Payment\Entities\PaymentMethod;
use Adyen\Core\Tests\Infrastructure\ORM\Entity\GenericEntityTest;

class PaymentMethodTest extends GenericEntityTest
{

    /**
     * @inheritDoc
     */
    public function getEntityClass(): string
    {
        return PaymentMethod::getClassName();
    }
}
