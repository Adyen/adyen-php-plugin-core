<?php

namespace Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MockComponents;

use Adyen\Core\BusinessLogic\DataAccess\Payment\Contracts\PaymentsRepository;
use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MemoryRepositoryWithConditionalDelete;
use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MockConditionalDelete;

class MockPaymentRepository extends MemoryRepositoryWithConditionalDelete implements PaymentsRepository
{
    use MockConditionalDelete;

    const THIS_CLASS_NAME = __CLASS__;
}
