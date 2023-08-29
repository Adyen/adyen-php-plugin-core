<?php
/** @noinspection PhpDuplicateArrayKeysInspection */

namespace Adyen\Core\Tests\Infrastructure\TaskExecution;

use Adyen\Core\Infrastructure\Configuration\ConfigEntity;
use Adyen\Core\Infrastructure\Configuration\Configuration;
use Adyen\Core\Infrastructure\Configuration\ConfigurationManager;
use Adyen\Core\Infrastructure\Logger\Interfaces\DefaultLoggerAdapter;
use Adyen\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Adyen\Core\Infrastructure\ORM\RepositoryRegistry;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusChangeException;
use Adyen\Core\Infrastructure\TaskExecution\RunnerStatusStorage;
use Adyen\Core\Infrastructure\TaskExecution\TaskRunnerStatus;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestDefaultLogger;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestConfigurationManager;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestShopConfiguration;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;
use PHPUnit\Framework\TestCase;

class TaskRunnerStatusStorageTest extends TestCase
{
    /** @var \Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestShopConfiguration */
    private $configuration;

    /**
     *
     * @throws \Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryClassException
     */
    protected function setUp(): void
    {
        $configuration = new TestShopConfiguration();

        new TestServiceRegister(
            array(
                ConfigurationManager::CLASS_NAME => function () {
                    return new TestConfigurationManager();
                },
                TimeProvider::CLASS_NAME => function () {
                    return new TestTimeProvider();
                },
                DefaultLoggerAdapter::CLASS_NAME => function () {
                    return new TestDefaultLogger();
                },
                ShopLoggerAdapter::CLASS_NAME => function () {
                    return new TestShopLogger();
                },
                Configuration::CLASS_NAME => function () use ($configuration) {
                    return $configuration;
                },
            )
        );

        $this->configuration = $configuration;

        RepositoryRegistry::registerRepository(ConfigEntity::CLASS_NAME, MemoryRepository::getClassName());
    }

    /**
     * @throws \Adyen\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException
     */
    public function testSetTaskRunnerWhenItExist()
    {
        $taskRunnerStatusStorage = new RunnerStatusStorage();
        $this->configuration->setTaskRunnerStatus('guid', 123456789);
        $taskStatus = new TaskRunnerStatus('guid', 123456789);
        $ex = null;

        try {
            $taskRunnerStatusStorage->setStatus($taskStatus);
        } catch (Exception $ex) {
            $this->fail('Set task runner status storage should not throw exception.');
        }

        $this->assertEmpty($ex);
    }

    /**
     * @throws \Adyen\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException
     */
    public function testSetTaskRunnerWhenItExistButItIsNotTheSame()
    {
        $this->expectException(TaskRunnerStatusChangeException::class);

        $taskRunnerStatusStorage = new RunnerStatusStorage();
        $this->configuration->setTaskRunnerStatus('guid', 123456789);
        $taskStatus = new TaskRunnerStatus('guid2', 123456789);

        $taskRunnerStatusStorage->setStatus($taskStatus);
    }
}
