<?php

namespace Adyen\Core\BusinessLogic\Bootstrap\Aspect;

/**
 * Interface Aspect
 *
 * @package Adyen\Core\BusinessLogic\Bootstrap\Aspect
 */
interface Aspect
{
    public function applyOn(callable $callee, array $params = []);
}
