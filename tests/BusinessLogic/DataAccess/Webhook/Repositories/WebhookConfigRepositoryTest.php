<?php

namespace Adyen\Core\Tests\BusinessLogic\DataAccess\Webhook\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\Webhook\Entities\WebhookConfig;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookConfig as WebhookConfigModel;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

class WebhookConfigRepositoryTest extends BaseTestCase
{
    private $repository;
    private $webhookConfigRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(WebhookConfig::getClassName());
        $this->webhookConfigRepository = TestServiceRegister::getService(WebhookConfigRepository::class);
    }

    public function testGetWebhookConfigNoWebhookConfig(): void
    {
        // act
        $result = StoreContext::doWithStore('1', [$this->webhookConfigRepository, 'getWebhookConfig']);

        // assert
        self::assertNull($result);
    }

    public function testGetWebhookConfig(): void
    {
        // arrange
        $webhookConfig = new WebhookConfigModel('1234', 'testMerchantId', true, 'username', 'password');
        $entity = new WebhookConfig();
        $entity->setStoreId('1');
        $entity->setWebhookConfig($webhookConfig);
        $this->repository->save($entity);

        // act
        $result = StoreContext::doWithStore('1', [$this->webhookConfigRepository, 'getWebhookConfig']);

        // assert
        self::assertEquals($webhookConfig, $result);
    }

    public function testGetWebhookConfigSetForDifferentStore(): void
    {
        // arrange
        $webhookConfig = new WebhookConfigModel('1234', 'testMerchantId', true, 'username', 'password');
        $entity = new WebhookConfig();
        $entity->setStoreId('1');
        $entity->setWebhookConfig($webhookConfig);
        $this->repository->save($entity);

        // act
        $result = StoreContext::doWithStore('2', [$this->webhookConfigRepository, 'getWebhookConfig']);

        // assert
        self::assertNull($result);
    }

    public function testSetWebhookConfig(): void
    {
        // arrange
        $webhookConfig = new WebhookConfigModel('1234', 'testMerchantId', true, 'username', 'password');

        // act
        StoreContext::doWithStore('1', [$this->webhookConfigRepository, 'setWebhookConfig'], [$webhookConfig]);

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($webhookConfig, $savedEntity[0]->getWebhookConfig());
    }

    public function testSetWebhookConfigAlreadyExists(): void
    {
        // arrange
        $webhookConfig = new WebhookConfigModel('1234', 'testMerchantId', true, 'username', 'password');
        $entity = new WebhookConfig();
        $entity->setStoreId('1');
        $entity->setWebhookConfig($webhookConfig);
        $this->repository->save($entity);
        $newWebhookConfig = new WebhookConfigModel('1234', 'testMerchantId', true, 'username1', 'password1');

        // act
        StoreContext::doWithStore('1', [$this->webhookConfigRepository, 'setWebhookConfig'], [$newWebhookConfig]);

        // assert
        $savedEntity = $this->repository->selectOne();
        self::assertEquals($newWebhookConfig, $savedEntity->getWebhookConfig());
    }

    public function testSetWebhookConfigAlreadyExistsForOtherStore(): void
    {
        // arrange
        $webhookConfig = new WebhookConfigModel('1234', 'testMerchantId', true, 'username', 'password');
        $entity = new WebhookConfig();
        $entity->setStoreId('1');
        $entity->setWebhookConfig($webhookConfig);
        $this->repository->save($entity);
        $newWebhookConfig = new WebhookConfigModel('1234', 'testMerchantId', true, 'username1', 'password1');

        // act
        StoreContext::doWithStore('2', [$this->webhookConfigRepository, 'setWebhookConfig'], [$newWebhookConfig]);

        // assert
        $savedEntity = $this->repository->select();
        self::assertCount(2, $savedEntity);
    }
}
