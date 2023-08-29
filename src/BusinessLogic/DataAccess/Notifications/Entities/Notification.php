<?php

namespace Adyen\Core\BusinessLogic\DataAccess\Notifications\Entities;

use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Adyen\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Adyen\Core\Infrastructure\ORM\Configuration\IndexMap;
use Adyen\Core\Infrastructure\ORM\Entity;

/**
 * Class Notification
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\Notifications\Entities
 */
class Notification extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    protected $storeId;

    /**
     * @var string
     */
    protected $orderId;

    /**
     * @var string
     */
    protected $paymentMethod;

    /**
     * @var string
     */
    protected $severity;

    /**
     * @var int
     */
    protected $timestamp = 0;

    /**
     * @var TranslatableLabel
     */
    protected $message;

    /**
     * @var TranslatableLabel
     */
    protected $details;

    /**
     * @var string[]
     */
    protected $fields = ['id', 'storeId', 'orderId', 'paymentMethod', 'severity', 'timestamp'];

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId')
            ->addIntegerIndex('timestamp')
            ->addStringIndex('severity');

        return new EntityConfiguration($indexMap, 'Notification');
    }

    /**
     * @inheritDoc
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $message = $data['message'];
        $details = $data['details'];

        $this->message = new TranslatableLabel(
            self::getDataValue($message, 'message'),
            self::getDataValue($message, 'code')
        );

        $this->details = new TranslatableLabel(
            self::getDataValue($details, 'message'),
            self::getDataValue($details, 'code')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['message'] = $this->translatableLabelToArray($this->message);
        $data['details'] = $this->translatableLabelToArray($this->details);

        return $data;
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @param string $storeId
     */
    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     */
    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     */
    public function setPaymentMethod(string $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return string
     */
    public function getSeverity(): string
    {
        return $this->severity;
    }

    /**
     * @param string $severity
     */
    public function setSeverity(string $severity): void
    {
        $this->severity = $severity;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return (int)$this->timestamp;
    }

    /**
     * @param int $timestamp
     */
    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return TranslatableLabel
     */
    public function getMessage(): TranslatableLabel
    {
        return $this->message;
    }

    /**
     * @param TranslatableLabel $message
     */
    public function setMessage(TranslatableLabel $message): void
    {
        $this->message = $message;
    }

    /**
     * @return TranslatableLabel
     */
    public function getDetails(): TranslatableLabel
    {
        return $this->details;
    }

    /**
     * @param TranslatableLabel $details
     */
    public function setDetails(TranslatableLabel $details): void
    {
        $this->details = $details;
    }

    /**
     * @param TranslatableLabel $label
     *
     * @return array
     */
    private function translatableLabelToArray(TranslatableLabel $label): array
    {
        return [
            'message' => $label->getMessage(),
            'code' => $label->getCode()
        ];
    }
}
