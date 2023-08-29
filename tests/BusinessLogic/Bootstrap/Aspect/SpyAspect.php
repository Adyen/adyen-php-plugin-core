<?php

namespace Adyen\Core\Tests\BusinessLogic\Bootstrap\Aspect;

use Adyen\Core\BusinessLogic\Bootstrap\Aspect\Aspect;

/**
 * Class SpyAspect
 *
 * @package Adyen\Core\Tests\BusinessLogic\Bootstrap\Aspect
 */
class SpyAspect implements Aspect
{
    /**
     * @var bool
     */
    private $isCalled = false;

    public function applyOn(callable $callee, array $params = [])
    {
        $this->isCalled = true;

        return call_user_func_array($callee, $params);
    }

    /**
     * @return bool
     */
    public function isCalled(): bool
    {
        return $this->isCalled;
    }
}
