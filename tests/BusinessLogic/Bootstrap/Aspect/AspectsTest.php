<?php

namespace Adyen\Core\Tests\BusinessLogic\Bootstrap\Aspect;

use Adyen\Core\BusinessLogic\Bootstrap\Aspect\Aspects;
use Adyen\Core\Infrastructure\Exceptions\ServiceNotRegisteredException;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use PHPUnit\Framework\TestCase;

/**
 * Class AspectsTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\Bootstrap\Aspect
 */
class AspectsTest extends TestCase
{
    public function setUp(): void
    {
        new TestServiceRegister();
    }

    public function testAspectAndSubjectAreTriggeredForInstance(): void
    {
        $subject = new FooTask();
        $aspect = new SpyAspect();

        Aspects::run($aspect)->beforeEachMethodOfInstance($subject)->execute();

        self::assertTrue($aspect->isCalled());
        self::assertEquals(1, $subject->getMethodCallCount('execute'));
    }

    public function testAspectAndSubjectAreTriggeredForService(): void
    {
        $subject = new FooTask();
        TestServiceRegister::registerService(FooTask::class, static function () use ($subject) {
            return $subject;
        });
        $aspect = new SpyAspect();

        Aspects::run($aspect)->beforeEachMethodOfService(FooTask::class)->execute();

        self::assertTrue($aspect->isCalled());
        self::assertEquals(1, $subject->getMethodCallCount('execute'));
    }

    public function testAspectIsTriggeredForUnregisteredService(): void
    {
        $aspect = new SpyAspect();
        $triggeredException = null;

        try {
            Aspects::run($aspect)->beforeEachMethodOfService(FooTask::class)->execute();
        } catch (\Throwable $exception) {
            $triggeredException = $exception;
        }

        self::assertTrue($aspect->isCalled());
        self::assertNotNull($triggeredException);
        self::assertInstanceOf(ServiceNotRegisteredException::class, $triggeredException);
    }

    public function testAspectComposition(): void
    {
        $subject = new FooTask();
        $aspect1 = new SpyAspect();
        $aspect2 = new SpyAspect();
        $aspect3 = new SpyAspect();

        Aspects
            ::run($aspect1)
            ->andRun($aspect2)
            ->andRun($aspect3)
            ->beforeEachMethodOfInstance($subject)
            ->execute();

        self::assertTrue($aspect1->isCalled());
        self::assertTrue($aspect2->isCalled());
        self::assertTrue($aspect3->isCalled());
        self::assertEquals(1, $subject->getMethodCallCount('execute'));
    }
}
