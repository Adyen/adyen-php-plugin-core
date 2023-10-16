<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Checkout\PaymentLink\Requests;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ApplicationInfo\ExternalPlatform;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ApplicationInfo\MerchantApplication;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\LineItem;

/**
 * Class PaymentLinkHttpRequest.
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Checkout\PaymentLink\Requests
 */
class PaymentLinkHttpRequest extends HttpRequest
{
    /**
     * @var PaymentLinkRequest
     */
    private $request;

    /**
     * @param PaymentLinkRequest $request
     */
    public function __construct(PaymentLinkRequest $request)
    {
        $this->request = $request;

        parent::__construct('/paymentLinks', $this->transformBody());
    }

    /**
     * Transforms webhook request to array.
     *
     * @return array
     */
    public function transformBody(): array
    {
        $body = [
            'amount' => [
                'value' => $this->request->getAmount()->getValue(),
                'currency' => (string)$this->request->getAmount()->getCurrency(),
            ],
            'merchantAccount' => $this->request->getMerchantAccount(),
            'reference' => $this->request->getReference()
        ];

        if (!empty($this->request->getAllowedPaymentMethods())) {
            $body['allowedPaymentMethods'] = $this->request->getAllowedPaymentMethods();
        }

        if ($this->request->getCountryCode() !== null) {
            $body['countryCode'] = $this->request->getCountryCode();
        }

        if ($this->request->getShopperReference() !== null) {
            $body['shopperReference'] = $this->request->getShopperReference();
        }

        if ($this->request->getShopperEmail() !== null) {
            $body['shopperEmail'] = $this->request->getShopperEmail();
        }

        if ($this->request->getShopperLocale() !== null) {
            $body['shopperLocale'] = $this->request->getShopperLocale();
        }

        if ($this->request->getShopperName() !== null) {
            $body['shopperName'] = [
                'firstName' => $this->request->getShopperName()->getFirstName(),
                'lastName' => $this->request->getShopperName()->getLastName(),
            ];
        }

        if ($this->request->getBillingAddress() !== null) {
            $body['billingAddress'] = [
                'city' => $this->request->getBillingAddress()->getCity(),
                'country' => $this->request->getBillingAddress()->getCountry(),
                'houseNumberOrName' => $this->request->getBillingAddress()->getHouseNumberOrName(),
                'postalCode' => $this->request->getBillingAddress()->getPostalCode(),
                'stateOrProvince' => $this->request->getBillingAddress()->getStateOrProvince(),
                'street' => $this->request->getBillingAddress()->getStreet(),
            ];
        }

        if ($this->request->getDeliveryAddress() !== null) {
            $body['deliveryAddress'] = [
                'city' => $this->request->getDeliveryAddress()->getCity(),
                'country' => $this->request->getDeliveryAddress()->getCountry(),
                'houseNumberOrName' => $this->request->getDeliveryAddress()->getHouseNumberOrName(),
                'postalCode' => $this->request->getDeliveryAddress()->getPostalCode(),
                'stateOrProvince' => $this->request->getDeliveryAddress()->getStateOrProvince(),
                'street' => $this->request->getDeliveryAddress()->getStreet(),
            ];
        }

        if ($this->request->getCaptureDelayHours() >= 0) {
            $body['captureDelayHours'] = $this->request->getCaptureDelayHours();
        }

        if ($this->request->getManualCapture()) {
            $body['manualCapture'] = $this->request->getManualCapture();
        }

        if ($this->request->getDateOfBirth() !== null) {
            $body['dateOfBirth'] = $this->request->getDateOfBirth();
        }

        if (!empty($this->request->getApplicationInfo())) {
            $this->request->getApplicationInfo()->getExternalPlatform(
            ) && $body['applicationInfo']['externalPlatform'] = $this->getFormattedExternalPlatform(
                $this->request->getApplicationInfo()->getExternalPlatform()
            );

            $this->request->getApplicationInfo()->getMerchantApplication(
            ) && $body['applicationInfo']['merchantApplication'] = $this->getFormattedMerchantApplication(
                $this->request->getApplicationInfo()->getMerchantApplication()
            );
        }

        if ($this->request->getLineItems() !== []) {
            $lineItems = [];

            foreach ($this->request->getLineItems() as $lineItem) {
                $lineItems[] = $this->getFormattedLineItem($lineItem);
            }

            $body['lineItems'] = $lineItems;
        }

        return $body;
    }



    /**
     * @param ExternalPlatform $externalPlatform
     *
     * @return array
     */
    private function getFormattedExternalPlatform(ExternalPlatform $externalPlatform): array
    {
        return [
            'name' => $externalPlatform->getName(),
            'version' => $externalPlatform->getVersion(),
            'integrator' => $externalPlatform->getIntegrator()
        ];
    }

    /**
     * @param MerchantApplication $merchantApplication
     *
     * @return array
     */
    private function getFormattedMerchantApplication(MerchantApplication $merchantApplication): array
    {
        return [
            'name' => $merchantApplication->getName(),
            'version' => $merchantApplication->getVersion()
        ];
    }

    /**
     * @param LineItem $lineItem
     *
     * @return array
     */
    private function getFormattedLineItem(LineItem $lineItem): array
    {
        return [
            'id' => $lineItem->getId(),
            'amountExcludingTax' => (string)$lineItem->getAmountExcludingTax(),
            'amountIncludingTax' => (string)$lineItem->getAmountIncludingTax(),
            'taxAmount' => (string)$lineItem->getTaxAmount(),
            'taxPercentage' => (string)$lineItem->getTaxPercentage(),
            'description' => $lineItem->getDescription(),
            'imageUrl' => $lineItem->getImageUrl(),
            'itemCategory' => $lineItem->getItemCategory(),
            'quantity' => $lineItem->getQuantity(),
        ];
    }
}
