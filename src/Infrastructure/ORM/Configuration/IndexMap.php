<?php

namespace Adyen\Core\Infrastructure\ORM\Configuration;

/**
 * Represents a map of all columns that are indexed.
 *
 * @package Adyen\Core\Infrastructure\ORM\Configuration
 */
class IndexMap
{
    /**
     * Array of indexed columns.
     *
     * @var IndexColumn[]
     */
    private $indexes = array();

    /**
     * Adds boolean index.
     *
     * @param string $name Column name for index.
     *
     * @return self This instance for chaining.
     */
    public function addBooleanIndex($name)
    {
        return $this->addIndex(new IndexColumn(IndexColumn::BOOLEAN, $name));
    }

    /**
     * Adds datetime index.
     *
     * @param string $name Column name for index.
     *
     * @return self This instance for chaining.
     */
    public function addDateTimeIndex($name)
    {
        return $this->addIndex(new IndexColumn(IndexColumn::DATETIME, $name));
    }

    /**
     * Adds double index.
     *
     * @param string $name Column name for index.
     *
     * @return self This instance for chaining.
     */
    public function addDoubleIndex($name)
    {
        return $this->addIndex(new IndexColumn(IndexColumn::DOUBLE, $name));
    }

    /**
     * Adds integer index.
     *
     * @param string $name Column name for index.
     *
     * @return self This instance for chaining.
     */
    public function addIntegerIndex($name)
    {
        return $this->addIndex(new IndexColumn(IndexColumn::INTEGER, $name));
    }

    /**
     * Adds string index.
     *
     * @param string $name Column name for index.
     *
     * @return self This instance for chaining.
     */
    public function addStringIndex($name)
    {
        return $this->addIndex(new IndexColumn(IndexColumn::STRING, $name));
    }

    /**
     * Returns array of indexes.
     *
     * @return IndexColumn[] Array of indexes.
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * Adds index to map.
     *
     * @param IndexColumn $index Index to be added.
     *
     * @return self This instance for chaining.
     */
    protected function addIndex(IndexColumn $index)
    {
        $this->indexes[$index->getProperty()] = $index;

        return $this;
    }
}
