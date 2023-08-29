<?php

namespace Adyen\Core\BusinessLogic\DataAccess\Payment\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\Payment\Contracts\PaymentsRepository;
use Adyen\Core\BusinessLogic\DataAccess\Payment\Entities\PaymentMethod;
use Adyen\Core\BusinessLogic\DataAccess\Payment\Exceptions\PaymentMethodNotConfiguredException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod as PaymentMethodModel;
use Adyen\Core\BusinessLogic\Domain\Payment\Repositories\PaymentMethodConfigRepository as BasePaymentMethodConfigRepository;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ORM\QueryFilter\Operators;
use Adyen\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class PaymentMethodConfigRepository
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\Payment\Repositories
 */
class PaymentMethodConfigRepository implements BasePaymentMethodConfigRepository
{
    /**
     * @var PaymentsRepository
     */
    protected $repository;
    /**
     * @var StoreContext
     */
    protected $storeContext;

    /**
     * @param PaymentsRepository $repository
     * @param StoreContext $storeContext
     */
    public function __construct(PaymentsRepository $repository, StoreContext $storeContext)
    {
        $this->repository = $repository;
        $this->storeContext = $storeContext;
    }

    /**
     * @inheritDoc
     */
    public function getConfiguredPaymentMethods(): array
    {
        $entities = $this->getConfiguredPaymentMethodEntities();

        return array_map(static function (PaymentMethod $entity) {
            return $entity->getPaymentMethod();
        }, $entities);
    }

    /**
     * @inheritDoc
     */
    public function getConfiguredPaymentMethodsForAllShops(): array
    {
        $entities = $this->repository->select(new QueryFilter());

        return array_map(static function (PaymentMethod $entity) {
            return $entity->getPaymentMethod();
        }, $entities);
    }

    /**
     * @inheritDoc
     */
    public function getConfiguredPaymentMethodsEntities(): array
    {
        return $this->repository->select();
    }

    /**
     * @inheritDoc
     */
    public function getEnabledExpressCheckoutPaymentMethods(): array
    {
        $expressCheckoutCodes = [
            (string)PaymentMethodCode::applePay(),
            (string)PaymentMethodCode::googlePay(),
            (string)PaymentMethodCode::payWithGoogle(),
            (string)PaymentMethodCode::payPal(),
            (string)PaymentMethodCode::amazonPay(),
        ];

        $queryFilter = new QueryFilter();
        $queryFilter
            ->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())
            ->where('code', Operators::IN, $expressCheckoutCodes);

        /** @var PaymentMethod[] $entities */
        $entities = $this->repository->select($queryFilter);


        return array_values(
            array_filter(
                array_map(static function (PaymentMethod $entity) {
                    $paymentMethod = $entity->getPaymentMethod();

                    if ($paymentMethod->getAdditionalData() && $paymentMethod->getAdditionalData()->getDisplayButtonOn(
                        )) {
                        return $paymentMethod;
                    }

                    return null;
                }, $entities)
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodById(string $id): ?PaymentMethodModel
    {
        $entity = $this->getMethodEntityById($id);

        return $entity ? $entity->getPaymentMethod() : null;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodByCode(string $code): ?PaymentMethodModel
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())
            ->where('code', Operators::EQUALS, $code);
        /** @var PaymentMethod | null $entity */
        $entity = $this->repository->selectOne($queryFilter);

        return $entity ? $entity->getPaymentMethod() : null;
    }

    /**
     * @inheritDoc
     */
    public function saveMethodConfiguration(PaymentMethodModel $method): void
    {
        $entity = new PaymentMethod();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setMethodId($method->getMethodId());
        $entity->setCode($method->getCode());
        $entity->setPaymentMethod($method);

        $this->repository->save($entity);
    }

    /**
     * @inheritDoc
     */
    public function updateMethodConfiguration(PaymentMethodModel $method): void
    {
        $entity = $this->getMethodEntityById($method->getMethodId());

        if ($entity === null) {
            throw new PaymentMethodNotConfiguredException(
                new TranslatableLabel(
                    'Payment method with id %s is not configured', 'payments.notConfigured',
                    [$method->getMethodId()]
                )
            );
        }

        $entity->setPaymentMethod($method);
        $this->repository->update($entity);
    }

    /**
     * @inheritDoc
     */
    public function deletePaymentMethodById(string $id): void
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())
            ->where('methodId', Operators::EQUALS, $id);

        $this->repository->deleteWhere($queryFilter);
    }

    /**
     * @inheritDoc
     */
    public function deleteConfiguredMethods(): void
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        $this->repository->deleteWhere($queryFilter);
    }

    /**
     * @param string $id
     *
     * @return PaymentMethod|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getMethodEntityById(string $id): ?PaymentMethod
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())
            ->where('methodId', Operators::EQUALS, $id);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }

    /**
     * @return PaymentMethod[]
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getConfiguredPaymentMethodEntities(): array
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        return $this->repository->select($queryFilter);
    }
}
