<?php

namespace Adyen\Core\Tests\BusinessLogic\Common;

use Adyen\Core\Infrastructure\Serializer\Concrete\JsonSerializer;
use Adyen\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use Adyen\Core\Infrastructure\Serializer\Serializer;
use Adyen\Core\Infrastructure\ServiceRegister;

/**
 * Class BaseSerializationTestCase
 *
 * @package Adyen\Core\Tests\BusinessLogic\Common
 */
class BaseSerializationTestCase extends BaseTestCase
{
    protected $serializable;
    public function testNativeSerialization(): void
    {
        ServiceRegister::registerService(Serializer::CLASS_NAME, static function () {
            return new NativeSerializer();
        });

        self::assertEquals($this->serializable, Serializer::unserialize(Serializer::serialize($this->serializable)));
    }

    public function testJsonSerialization(): void
    {
        ServiceRegister::registerService(Serializer::CLASS_NAME, static function () {
            return new JsonSerializer();
        });

        self::assertEquals($this->serializable, Serializer::unserialize(Serializer::serialize($this->serializable)));
    }
}
