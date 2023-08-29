<?php

namespace Adyen\Core\Tests\BusinessLogic\DataAccess\Payment\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\Payment\Entities\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod as PaymentMethodModel;
use Adyen\Core\BusinessLogic\Domain\Payment\Repositories\PaymentMethodConfigRepository;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

class PaymentMethodConfigRepositoryTest extends BaseTestCase
{
    public $repository;

    public $methodConfigRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(PaymentMethod::getClassName());
        $this->methodConfigRepository = TestServiceRegister::getService(PaymentMethodConfigRepository::class);
    }

    public function testGetConfiguredPaymentMethodsNoMethods(): void
    {
        // act
        $result = StoreContext::doWithStore('1', [$this->methodConfigRepository, 'getConfiguredPaymentMethods']);

        // assert
        self::assertEmpty($result);
    }

    public function testGetConfiguredPaymentMethods(): void
    {
        // arrange
        $pm1 = new PaymentMethodModel(
            '1234',
            'code',
            'name',
            'logo',
            true,
            [],
            [],
            'type'
        );
        $entity1 = new PaymentMethod();
        $entity1->setStoreId('1');
        $entity1->setMethodId('1234');
        $entity1->setCode('code');
        $entity1->setPaymentMethod($pm1);
        $this->repository->save($entity1);
        $pm2 = new PaymentMethodModel(
            '2345',
            'code',
            'name1',
            'logo1',
            true,
            [],
            [],
            'type'
        );
        $entity2 = new PaymentMethod();
        $entity2->setStoreId('1');
        $entity2->setMethodId('2345');
        $entity2->setPaymentMethod($pm2);
        $entity2->setCode('code');
        $this->repository->save($entity2);

        // act
        $result = StoreContext::doWithStore('1', [$this->methodConfigRepository, 'getConfiguredPaymentMethods']);

        // assert
        self::assertEquals([$pm1, $pm2], $result);
    }

    public function testGetConfiguredPaymentMethodsMethodsConfiguredForOtherStore(): void
    {
        // arrange
        $pm1 = new PaymentMethodModel(
            '1234',
            'code',
            'name',
            'logo',
            true,
            ['EUR'],
            ['FR'],
            'type'

        );
        $entity1 = new PaymentMethod();
        $entity1->setStoreId('1');
        $entity1->setMethodId('1234');
        $entity1->setCode('code');
        $entity1->setPaymentMethod($pm1);
        $this->repository->save($entity1);
        $pm2 = new PaymentMethodModel(
            '2345',
            'code',
            'name1',
            'logo1',
            true,
            ['USD'],
            ['US'],
            'type'
        );
        $entity2 = new PaymentMethod();
        $entity2->setStoreId('1');
        $entity2->setMethodId('2345');
        $entity2->setCode('code');
        $entity2->setPaymentMethod($pm2);
        $this->repository->save($entity2);

        // act
        $result = StoreContext::doWithStore('2', [$this->methodConfigRepository, 'getConfiguredPaymentMethods']);

        // assert
        self::assertEmpty($result);
    }
}
