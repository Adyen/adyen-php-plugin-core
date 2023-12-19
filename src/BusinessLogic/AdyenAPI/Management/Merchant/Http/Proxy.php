<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Management\Merchant\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Authorized\AuthorizedProxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\Domain\Merchant\Models\Merchant;
use Adyen\Core\BusinessLogic\Domain\Merchant\Proxies\MerchantProxy;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use Adyen\Core\Infrastructure\Logger\Logger;

/**
 * Class Proxy
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Merchant\Http
 */
class Proxy extends AuthorizedProxy implements MerchantProxy
{
    /**
     * @inheritDoc
     */
    public function getMerchants(): array
    {
        try {
            $page = 1;
            $response = $this->getMerchantsByPage($page);
            $result = $this->transformBodyToMerchant($response['data'] ?? []);
            while (!empty($response['data'])) {
                $page++;
                $response = $this->getMerchantsByPage($page);
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $result = array_merge($result, $this->transformBodyToMerchant($response['data'] ?? []));
            }
        } catch (HttpRequestException $e) {
            Logger::logError($e->getMessage());

            return [];
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getMerchantById(string $merchantId): ?Merchant
    {
        $request = new HttpRequest("/merchants/$merchantId");

        $response = $this->get($request);
        $responseBody = $response->decodeBodyToArray();

        return new Merchant(
            $responseBody['id'] ?? '',
            $responseBody['name'] ?? '',
            isset($responseBody['dataCenters']) ? reset($responseBody['dataCenters'])['livePrefix'] : '',
            ''
        );
    }

    /**
     * @inheritDoc
     */
    public function generateClientKey(): string
    {
        $request = new HttpRequest("/me/generateClientKey");

        $response = $this->post($request);
        $responseBody = $response->decodeBodyToArray();

        return $responseBody['clientKey'] ?? '';
    }

    /**
     * @param array $data
     *
     * @return Merchant[]
     */
    private function transformBodyToMerchant(array $data): array
    {
        $merchants = [];

        foreach ($data as $merchant) {
            if (empty($merchant['name']) || empty($merchant['id'])) {
                Logger::logWarning('Merchant data are not valid');

                continue;
            }

            $merchantName = $merchant['name'];
            $merchantId = $merchant['id'];
            $company = $merchant['companyId'];
            $merchants[] = new Merchant($merchantId, $merchantName, '', $company);
        }

        return $merchants;
    }

    /**
     * @param int $page
     *
     * @return array
     *
     * @throws HttpRequestException
     */
    private function getMerchantsByPage(int $page): array
    {
        $request = new HttpRequest(
            '/merchants',
            [],
            [
                'pageNumber' => $page,
                'pageSize' => 100
            ]
        );

        return $this->get($request)->decodeBodyToArray();
    }
}
