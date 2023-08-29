<?php


namespace Adyen\Core\Tests\BusinessLogic\Common\MockComponents;


class Configuration extends \Adyen\Core\BusinessLogic\Domain\Configuration\Configuration
{
    public $setDebugModeCalled;
    /**
     * @inheritDoc
     */
    public function getIntegrationName()
    {
        return 'Test';
    }

    public function getIntegrationVersion()
    {
        return '5.2.8';
    }

    /**
     * @inheritDoc
     */
    public function getAsyncProcessUrl($guid)
    {
        return 'url';
    }

    public function setDebugModeEnabled($status)
    {
        $this->setDebugModeCalled = true;
        parent::setDebugModeEnabled($status);
    }

    public function getPluginName()
    {
        return 'AdyenTest';
    }

    public function getPluginVersion()
    {
        return '1.0.0';
    }
}
