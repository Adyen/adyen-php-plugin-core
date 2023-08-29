<?php

namespace Adyen\Core\Tests\BusinessLogic\Common;

use Adyen\Core\Infrastructure\Serializer\Concrete\JsonSerializer;
use Adyen\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use Adyen\Core\Infrastructure\Serializer\Serializer;
use Adyen\Core\Infrastructure\ServiceRegister;

class BaseSerializationTest extends BaseTestCase
{
    protected $serializable;

    public function testNativeSerialization()
    {
        ServiceRegister::registerService(Serializer::CLASS_NAME, function () {
            return new NativeSerializer();
        });

        self::assertEquals($this->serializable, Serializer::unserialize(Serializer::serialize($this->serializable)));
    }

    public function testJsonSerialization()
    {
        ServiceRegister::registerService(Serializer::CLASS_NAME, function () {
            return new JsonSerializer();
        });

        self::assertEquals($this->serializable, Serializer::unserialize(Serializer::serialize($this->serializable)));
    }
}
