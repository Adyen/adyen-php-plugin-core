<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\AdyenGivingSettings\Response;

/**
 * Class AdyenGivingSettingsPutResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\AdyenGivingSettings\Response
 */
class AdyenGivingSettingsPutResponse extends \Adyen\Core\BusinessLogic\AdminAPI\Response\Response
{
    /**
     * @return array
     */
    public function toArray(): array
    {
        return ['success' => true];
    }
}
