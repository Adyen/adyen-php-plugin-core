<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\WebhookNotifications\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Payment\Services\PaymentService;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use DateTimeInterface;

/**
 * Class WebhookNotificationResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\WebhookNotifications\Response
 */
class WebhookNotificationResponse extends Response
{
    /**
     * @var bool
     */
    private $hasNextPage;

    /**
     * @var TransactionLog[]
     */
    private $logs;

    /**
     * @param bool $hasNextPage
     * @param TransactionLog[] $logs
     */
    public function __construct(bool $hasNextPage, array $logs)
    {
        $this->hasNextPage = $hasNextPage;
        $this->logs = $logs;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'nextPageAvailable' => $this->hasNextPage,
            'notifications' => $this->logsToArray($this->logs)
        ];
    }

    /**
     * @param TransactionLog[] $logs
     *
     * @return array
     */
    private function logsToArray(array $logs): array
    {
        $logsToArray = [];

        foreach ($logs as $log) {
            $logsToArray[] = [
                'orderId' => $log->getMerchantReference(),
                'paymentMethod' => $log->getPaymentMethod(),
                'notificationId' => $log->getId(),
                'dateAndTime' => TimeProvider::getInstance()
                    ->getDateTime($log->getTimestamp())
                    ->format(DateTimeInterface::ATOM),
                'code' => $log->getEventCode(),
                'successful' => $log->isSuccessful(),
                'status' => $log->getQueueStatus(),
                'hasDetails' => !(empty($log->getReason()))
                    || !(empty($log->getFailureDescription()))
                    || !(empty($log->getAdyenLink()))
                    || !(empty($log->getShopLink())),
                'details' => [
                    'reason' => $log->getReason() ?? '',
                    'failureDescription' => $log->getFailureDescription() ?? '',
                    'adyenLink' => $log->getAdyenLink(),
                    'shopLink' => $log->getShopLink()
                ],
                'logo' => $this->getLogo($log->getPaymentMethod())
            ];
        }

        return $logsToArray;
    }

    /**
     * @param string $code
     *
     * @return string
     */
    private function getLogo(string $code): string
    {
        if (in_array($code, PaymentService::CREDIT_CARD_BRANDS, true)) {
            $code = PaymentService::CREDIT_CARD_CODE;
        }

        return PaymentMethod::getLogoUrl($code);
    }
}
