<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\BrowserInfo;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\PaymentRequestProcessor;

/**
 * Class BrowserInfoStateDataProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors
 */
class BrowserInfoStateDataProcessor implements PaymentRequestProcessor
{
    /**
     * @param PaymentRequestBuilder $builder
     * @param StartTransactionRequestContext $context
     *
     * @return void
     */
    public function process(PaymentRequestBuilder $builder, StartTransactionRequestContext $context): void
    {
        $browserInfo = $this->getDefaultBrowserInfo();
        $browserInfoData = $context->getStateData()->get('browserInfo');

        if ($browserInfoData) {
            $browserInfo = new BrowserInfo(
                $browserInfoData['acceptHeader'] ?? '',
              $browserInfoData['userAgent'] ?? '',
                $browserInfoData['colorDepth'] ?? 24,
                $browserInfoData['javaEnabled'] ?? true,
                $browserInfoData['language'] ?? 'en-US',
                $browserInfoData['screenHeight'] ?? 0,
                $browserInfoData['screenWidth'] ?? 0,
                $browserInfoData['timeZoneOffset'] ?? 0
            );
        }

        $builder->setBrowserInfo($browserInfo);
    }

    /**
     * @return BrowserInfo|null
     */
    protected function getDefaultBrowserInfo(): ?BrowserInfo
    {
        if (!empty($_SERVER['HTTP_ACCEPT'])) {
            return new BrowserInfo($_SERVER['HTTP_ACCEPT']);
        }

        return null;
    }
}
