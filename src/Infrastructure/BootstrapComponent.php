<?php

namespace Adyen\Core\Infrastructure;

use Adyen\Core\Infrastructure\Configuration\ConfigurationManager;
use Adyen\Core\Infrastructure\Http\CurlHttpClient;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\LoggingHttpclient;
use Adyen\Core\Infrastructure\TaskExecution\AsyncProcessStarterService;
use Adyen\Core\Infrastructure\TaskExecution\Events\QueueItemStateTransitionEventBus;
use Adyen\Core\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;
use Adyen\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerManager;
use Adyen\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerStatusStorage;
use Adyen\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use Adyen\Core\Infrastructure\TaskExecution\QueueService;
use Adyen\Core\Infrastructure\TaskExecution\RunnerStatusStorage;
use Adyen\Core\Infrastructure\TaskExecution\TaskRunner;
use Adyen\Core\Infrastructure\TaskExecution\TaskRunnerWakeupService;
use Adyen\Core\Infrastructure\Utility\Events\EventBus;
use Adyen\Core\Infrastructure\Utility\GuidProvider;
use Adyen\Core\Infrastructure\Utility\TimeProvider;

/**
 * Class BootstrapComponent.
 *
 * @package Adyen\Core\Infrastructure
 */
class BootstrapComponent
{
    /**
     * Initializes infrastructure components.
     */
    public static function init()
    {
        static::initServices();
        static::initRepositories();
        static::initEvents();
    }

    /**
     * Initializes services and utilities.
     */
    protected static function initServices()
    {
        ServiceRegister::registerService(
            ConfigurationManager::CLASS_NAME,
            function () {
                return ConfigurationManager::getInstance();
            }
        );
        ServiceRegister::registerService(
            TimeProvider::CLASS_NAME,
            function () {
                return TimeProvider::getInstance();
            }
        );
        ServiceRegister::registerService(
            GuidProvider::CLASS_NAME,
            function () {
                return GuidProvider::getInstance();
            }
        );
        ServiceRegister::registerService(
            EventBus::CLASS_NAME,
            function () {
                return EventBus::getInstance();
            }
        );
        ServiceRegister::registerService(
            AsyncProcessService::CLASS_NAME,
            function () {
                return AsyncProcessStarterService::getInstance();
            }
        );
        ServiceRegister::registerService(
            QueueService::CLASS_NAME,
            function () {
                return new QueueService();
            }
        );
        ServiceRegister::registerService(
            TaskRunnerWakeup::CLASS_NAME,
            function () {
                return new TaskRunnerWakeupService();
            }
        );
        ServiceRegister::registerService(
            TaskRunner::CLASS_NAME,
            function () {
                return new TaskRunner();
            }
        );
        ServiceRegister::registerService(
            TaskRunnerStatusStorage::CLASS_NAME,
            function () {
                return new RunnerStatusStorage();
            }
        );
        ServiceRegister::registerService(
            TaskRunnerManager::CLASS_NAME,
            function () {
                return new TaskExecution\TaskRunnerManager();
            }
        );
        ServiceRegister::registerService(
            HttpClient::CLASS_NAME,
            function () {
                return new LoggingHttpclient(new CurlHttpClient());
            }
        );
        ServiceRegister::registerService(
            QueueItemStateTransitionEventBus::CLASS_NAME,
            function () {
                return QueueItemStateTransitionEventBus::getInstance();
            }
        );
    }

    /**
     * Initializes repositories.
     */
    protected static function initRepositories()
    {
    }

    /**
     * Initializes events.
     */
    protected static function initEvents()
    {
    }
}
