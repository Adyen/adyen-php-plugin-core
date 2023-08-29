<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Request;

/**
 * Class Request
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Request
 */
abstract class Request
{
    /**
     * Transform to Domain model based on data sent from controller.
     *
     * @return object
     */
    abstract public function transformToDomainModel(): object;
}
