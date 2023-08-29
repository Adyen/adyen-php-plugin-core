<?php

namespace Adyen\Core\Tests\Infrastructure\Common;

use Adyen\Core\Infrastructure\ServiceRegister;

/**
 * Class TestServiceRegister.
 *
 * @package Adyen\Core\Tests\Infrastructure\Common
 */
class TestServiceRegister extends ServiceRegister
{
    /**
     * TestServiceRegister constructor.
     *
     * @inheritdoc
     */
    public function __construct(array $services = array())
    {
        // changing visibility so that Services could be reset in tests.
        parent::__construct($services);
    }
}
