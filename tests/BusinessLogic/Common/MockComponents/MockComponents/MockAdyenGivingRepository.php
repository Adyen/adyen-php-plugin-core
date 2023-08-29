<?php

namespace Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MockComponents;

use Adyen\Core\BusinessLogic\DataAccess\AdyenGiving\Contracts\AdyenGivingRepository;
use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MemoryRepositoryWithConditionalDelete;
use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MockConditionalDelete;

class MockAdyenGivingRepository extends MemoryRepositoryWithConditionalDelete implements AdyenGivingRepository
{
    use MockConditionalDelete;

    const THIS_CLASS_NAME = __CLASS__;
}
