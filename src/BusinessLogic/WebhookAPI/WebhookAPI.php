<?php

namespace Adyen\Core\BusinessLogic\WebhookAPI;

use Adyen\Core\BusinessLogic\AdminAPI\Aspects\ErrorHandlingAspect;
use Adyen\Core\BusinessLogic\AdminAPI\Aspects\StoreContextAspect;
use Adyen\Core\BusinessLogic\Bootstrap\Aspect\Aspects;
use Adyen\Core\BusinessLogic\WebhookAPI\Controller\WebhookController;

/**
 * Class WebhookAPI
 *
 * @package Adyen\Core\BusinessLogic\WebhookAPI
 */
class WebhookAPI
{
    private function __construct()
    {
    }

    /**
     * @return WebhookAPI
     */
    public static function get(): object
    {
        return Aspects::run(new ErrorHandlingAspect())->beforeEachMethodOfInstance(new WebhookAPI());
    }

    /**
     * @return WebhookController
     */
    public function webhookHandler(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(WebhookController::class);
    }
}
