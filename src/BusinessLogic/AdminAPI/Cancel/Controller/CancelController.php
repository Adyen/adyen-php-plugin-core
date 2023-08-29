<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Cancel\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\Cancel\Response\CancelResponse;
use Adyen\Core\BusinessLogic\Domain\Cancel\Handlers\CancelHandler;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;

/**
 * Class CancelController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Cancel\Controller
 */
class CancelController
{
    /**
     * @var CancelHandler
     */
    private $handler;

    /**
     * @param CancelHandler $cancelHandler
     */
    public function __construct(CancelHandler $cancelHandler)
    {
        $this->handler = $cancelHandler;
    }

    /**
     * @param string $merchantReference
     *
     * @return CancelResponse True if cancel request was received by Adyen successfully; false otherwise. Use transaction log
     * for final action outcome.
     *
     * @throws InvalidMerchantReferenceException
     */
    public function handle(string $merchantReference): CancelResponse
    {
        return new CancelResponse($this->handler->handle($merchantReference));
    }
}
