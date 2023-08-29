<?php

namespace Adyen\Core\Tests\Infrastructure\ORM\Entity;

use Adyen\Core\Infrastructure\ORM\Configuration\Index;
use Adyen\Core\Infrastructure\ORM\Entity;
use PHPUnit\Framework\TestCase;

/**
 * Class GenericEntityTest
 * @package Adyen\Core\Tests\Infrastructure\ORM\Entity
 */
abstract class GenericEntityTest extends TestCase
{
    public static $ALLOWED_INDEX_TYPES = array(
        'integer',
        'double',
        'dateTime',
        'string',
        'array',
        'boolean',
    );

    /**
     * Returns entity full class name
     *
     * @return string
     */
    abstract public function getEntityClass();

    public function testEntityClass()
    {
        $entityClass = $this->getEntityClass();
        $entity = new $entityClass();

        $this->assertInstanceOf(Entity::getClassName(), $entity);

        return $entity;
    }

    /**
     * @depends testEntityClass
     *
     * @param Entity $entity
     */
    public function testEntityConfiguration($entity)
    {
        $config = $entity->getConfig();

        $type = $config->getType();
        $this->assertNotEmpty($type);
        $this->assertIsString($type);

        $indexMap = $config->getIndexMap();
        $this->assertInstanceOf("Adyen\Core\Infrastructure\ORM\Configuration\IndexMap", $indexMap);
        /**
         * @var string $key
         * @var \Adyen\Core\Infrastructure\ORM\Configuration\Index $item
         */
        foreach ($indexMap->getIndexes() as $key => $item) {
            $this->assertNotEmpty($item, "Index configuration for $key must not be empty.");
            $this->assertInstanceOf("Adyen\Core\Infrastructure\ORM\Configuration\Index", $item);

            $this->assertContains(
                $item->getType(),
                self::$ALLOWED_INDEX_TYPES,
                "Index type '{$item->getType()}' for field $key is not supported."
            );
        }
    }

    public function testInvalidIndexType()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Index('type', 'name');
    }
}
