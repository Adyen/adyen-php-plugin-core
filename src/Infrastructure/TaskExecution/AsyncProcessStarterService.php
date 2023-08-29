<?php

namespace Adyen\Core\Infrastructure\TaskExecution;

use Adyen\Core\Infrastructure\Configuration\Configuration;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Logger\Logger;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use Adyen\Core\Infrastructure\ORM\RepositoryRegistry;
use Adyen\Core\Infrastructure\ServiceRegister;
use Adyen\Core\Infrastructure\Singleton;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\ProcessStarterSaveException;
use Adyen\Core\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;
use Adyen\Core\Infrastructure\TaskExecution\Interfaces\Runnable;
use Adyen\Core\Infrastructure\Utility\GuidProvider;
use Exception;

/**
 * Class AsyncProcessStarter.
 *
 * @package Adyen\Core\Infrastructure\TaskExecution
 */
class AsyncProcessStarterService extends Singleton implements AsyncProcessService
{
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;
    /**
     * Configuration instance.
     *
     * @var Configuration
     */
    private $configuration;
    /**
     * Process entity repository.
     *
     * @var RepositoryInterface
     */
    private $processRepository;
    /**
     * GUID provider instance.
     *
     * @var GuidProvider
     */
    private $guidProvider;
    /**
     * HTTP client.
     *
     * @var HttpClient
     */
    private $httpClient;

    /**
     * AsyncProcessStarterService constructor.
     * @throws RepositoryNotRegisteredException
     */
    protected function __construct()
    {
        parent::__construct();

        $this->httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
        $this->guidProvider = ServiceRegister::getService(GuidProvider::CLASS_NAME);
        $this->configuration = ServiceRegister::getService(Configuration::CLASS_NAME);
        $this->processRepository = RepositoryRegistry::getRepository(Process::CLASS_NAME);
    }

    /**
     * Starts given runner asynchronously (in new process/web request or similar).
     *
     * @param Runnable $runner Runner that should be started async.
     *
     * @throws HttpRequestException
     * @throws ProcessStarterSaveException
     */
    public function start(Runnable $runner)
    {
        $guid = trim($this->guidProvider->generateGuid());

        $this->saveGuidAndRunner($guid, $runner);
        $this->startRunnerAsynchronously($guid);
    }

    /**
     * Runs a process with provided identifier.
     *
     * @param string $guid Identifier of process.
     */
    public function runProcess($guid)
    {
        try {
            $filter = new QueryFilter();
            $filter->where('guid', '=', $guid);

            /** @var Process $process */
            $process = $this->processRepository->selectOne($filter);
            if ($process !== null) {
                $process->getRunner()->run();
                $this->processRepository->delete($process);
            }
        } catch (Exception $e) {
            Logger::logError($e->getMessage(), 'Core', ['guid' => $guid, 'trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * Saves runner and guid to storage.
     *
     * @param string $guid Unique process identifier.
     * @param Runnable $runner Runner instance.
     *
     * @throws ProcessStarterSaveException
     */
    private function saveGuidAndRunner($guid, Runnable $runner)
    {
        try {
            $process = new Process();
            $process->setGuid($guid);
            $process->setRunner($runner);

            $this->processRepository->save($process);
        } catch (Exception $e) {
            Logger::logError($e->getMessage());
            throw new ProcessStarterSaveException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Starts runnable asynchronously.
     *
     * @param string $guid Unique process identifier.
     *
     * @throws HttpRequestException
     */
    private function startRunnerAsynchronously($guid)
    {
        try {
            $this->httpClient->requestAsync(
                $this->configuration->getAsyncProcessCallHttpMethod(),
                $this->configuration->getAsyncProcessUrl($guid)
            );
        } catch (Exception $e) {
            Logger::logError($e->getMessage(), 'Integration');
            throw new HttpRequestException($e->getMessage(), 0, $e);
        }
    }
}
