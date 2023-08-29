<?php

namespace Adyen\Core\Tests\BusinessLogic\DataAccess\Webhook\Entities;

use Adyen\Core\BusinessLogic\DataAccess\Webhook\Entities\WebhookConfig;
use Adyen\Core\Tests\Infrastructure\ORM\Entity\GenericEntityTest;

class WebhookConfigTest extends GenericEntityTest
{

    /**
     * @inheritDoc
     */
    public function getEntityClass(): string
    {
        return WebhookConfig::getClassName();
    }
}
