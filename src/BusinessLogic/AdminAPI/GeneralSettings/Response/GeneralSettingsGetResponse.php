<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\GeneralSettings\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;

/**
 * Class GeneralSettingsResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\GeneralSettings\Response
 */
class GeneralSettingsGetResponse extends Response
{
    /**
     * @var GeneralSettings
     */
    private $generalSettings;

    /**
     * @param GeneralSettings|null $generalSettings
     */
    public function __construct(?GeneralSettings $generalSettings)
    {
        $this->generalSettings = $generalSettings;
    }

    /**
     * Returns array representation of GeneralSettings.
     *
     * @return array Array representation of general settings.
     */
    public function toArray(): array
    {
        return $this->generalSettings ? $this->transformGeneralSettings() : [
            'basketItemSync' => false,
            'capture' => '',
            'captureDelay' => '',
            'shipmentStatus' => '',
            'retentionPeriod' => '60',
            'enablePayByLink' => false,
            'payByLinkTitle' => 'Adyen Pay By Link',
            'defaultLinkExpirationTime' => 7,
            'executeOrderUpdateSynchronously' => false,
            'cancelledPartialPayment' => true,
            'disabledOrderModificationsForFailedRefund' => false
        ];
    }

    /**
     * @return array
     */
    private function transformGeneralSettings(): array
    {
        return [
            'basketItemSync' => $this->generalSettings->isBasketItemSync(),
            'capture' => $this->generalSettings->getCapture()->getType(),
            'captureDelay' => $this->generalSettings->getCaptureDelay(),
            'shipmentStatus' => $this->generalSettings->getShipmentStatus(),
            'retentionPeriod' => $this->generalSettings->getRetentionPeriod(),
            'enablePayByLink' => $this->generalSettings->isEnablePayByLink(),
            'payByLinkTitle' => $this->generalSettings->getPayByLinkTitle(),
            'defaultLinkExpirationTime' => $this->generalSettings->getDefaultLinkExpirationTime(),
            'executeOrderUpdateSynchronously' => $this->generalSettings->isExecuteOrderUpdateSynchronously(),
            'cancelledPartialPayment' => $this->generalSettings->isCancelledPartialPayment(),
            'disabledOrderModificationsForFailedRefund' =>
                $this->generalSettings->areDisabledOrderModificationsForFailedRefund(),
        ];
    }
}
