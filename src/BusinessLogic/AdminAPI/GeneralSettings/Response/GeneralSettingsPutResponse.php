<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\GeneralSettings\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class GeneralSettingsPutResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\GeneralSettings\Response
 */
class GeneralSettingsPutResponse extends Response
{
    /**
     * @return array
     */
    public function toArray(): array
    {
        return ['success' => true];
    }
}
