<?php

namespace Adyen\Core\BusinessLogic\Domain\Connection\Models;

/**
 * Class ApiCredentials
 *
 * @package Adyen\Core\BusinessLogic\Domain\Connection\Models
 */
class ApiCredentials
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var bool
     */
    private $active;
    /**
     * @var string
     */
    private $company;

    /**
     * @param string $id
     * @param bool $active
     * @param string $company
     */
    public function __construct(string $id, bool $active, string $company)
    {
        $this->id = $id;
        $this->active = $active;
        $this->company = $company;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getCompany(): string
    {
        return $this->company;
    }
}
