<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

/**
 * Class PaymentMethodResponse
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class PaymentMethodResponse
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $type;
    /**
     * @var array
     */
    private $metaData;

    /**
     * @param string $name
     * @param string $type
     * @param array $metaData
     */
    public function __construct(string $name, string $type, array $metaData = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->metaData = $metaData;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getMetaData(): array
    {
        return $this->metaData;
    }
}
