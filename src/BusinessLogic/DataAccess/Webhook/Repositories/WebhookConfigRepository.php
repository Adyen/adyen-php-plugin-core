<?php

namespace Adyen\Core\BusinessLogic\DataAccess\Webhook\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\Webhook\Entities\WebhookConfig as WebhookConfigEntity;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookConfig;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository as BaseWebhookConfigRepository;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\Infrastructure\ORM\QueryFilter\Operators;
use Adyen\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class WebhookConfigRepository
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\Webhook\Repositories
 */
class WebhookConfigRepository implements BaseWebhookConfigRepository
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;
    /**
     * @var StoreContext
     */
    protected $storeContext;

    /**
     * @param RepositoryInterface $repository
     * @param StoreContext $storeContext
     */
    public function __construct(RepositoryInterface $repository, StoreContext $storeContext)
    {
        $this->repository = $repository;
        $this->storeContext = $storeContext;
    }

    /**
     * @inheritDoc
     */
    public function getWebhookConfig(): ?WebhookConfig
    {
        $entity = $this->getWebhookConfigEntity();

        return $entity ? $entity->getWebhookConfig() : null;
    }

    /**
     * @inheritDoc
     */
    public function setWebhookConfig(WebhookConfig $config): void
    {
        $existingConfig = $this->getWebhookConfigEntity();

        if ($existingConfig) {
            $existingConfig->setWebhookConfig($config);
            $this->repository->update($existingConfig);

            return;
        }

        $entity = new WebhookConfigEntity();
        $entity->setWebhookConfig($config);
        $entity->setStoreId($this->storeContext->getStoreId());
        $this->repository->save($entity);
    }

    /**
     * @inheritDoc
     */
    public function deleteWebhookConfig(): void
    {
        $config = $this->getWebhookConfigEntity();

        if (!$config) {
            return;
        }

        $this->repository->delete($config);
    }

    /**
     * @return WebhookConfigEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getWebhookConfigEntity(): ?WebhookConfigEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }
}
