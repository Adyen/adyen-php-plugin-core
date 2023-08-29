<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Payments\Requests;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AdditionalData\BasketItem;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AdditionalData\ItemDetailLine;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\LineItem;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentRequest;

/**
 * Class PaymentHttpRequest
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Payments\Requests
 */
class PaymentHttpRequest extends HttpRequest
{
    /**
     * @var PaymentRequest
     */
    private $request;

    public function __construct(PaymentRequest $request)
    {
        $this->request = $request;

        parent::__construct('/payments', $this->transformBody());
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
            'channel' => $this->request->getChannel(),
            'origin' => $this->request->getOrigin(),
            'merchantAccount' => $this->request->getMerchantId(),
            'reference' => $this->request->getReference(),
            'returnUrl' => $this->request->getReturnUrl(),
            'paymentMethod' => $this->request->getPaymentMethod(),
            'dateOfBirth' => $this->request->getDateOfBirth(),
            'telephoneNumber' => $this->request->getTelephoneNumber(),
            'shopperEmail' => $this->request->getShopperEmail(),
            'countryCode' => $this->request->getCountryCode(),
            'socialSecurityNumber' => $this->request->getSocialSecurityNumber(),
            'storePaymentMethod' => $this->request->isStorePaymentMethod(),
            'conversionId' => $this->request->getConversionId(),
            'shopperReference' => (string)$this->request->getShopperReference(),
            'shopperLocale' => $this->request->getShopperLocale(),
        ];

        if ($this->request->getShopperInteraction() !== null) {
            $body['shopperInteraction'] = $this->request->getShopperInteraction();
        }

        if ($this->request->getRecurringProcessingModel() !== null) {
            $body['recurringProcessingModel'] = $this->request->getRecurringProcessingModel();
        }

        if ($this->request->getBrowserInfo() !== null) {
            $body['browserInfo'] = $this->getFormattedBrowserInfo();
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

        if ($this->request->getAuthenticationData()) {
            $body["authenticationData"] = [
                "threeDSRequestData" => [
                    "nativeThreeDS" => $this->request->getAuthenticationData()->getNativeThreeDS()
                ]
            ];
        }

        if ($this->request->getRiskData() !== null) {
            $body['riskData'] = [
                'clientData' => $this->request->getRiskData()->getClientData(),
                'fraudOffset' => $this->request->getRiskData()->getFraudOffset(),
                'profileReference' => $this->request->getRiskData()->getProfileReference(),
            ];

            if ($this->request->getRiskData()->getCustomFields()) {
                $body['riskData']['customFields'] = $this->request->getRiskData()->getCustomFields();
            }
        }

        if ($this->request->getShopperName() !== null) {
            $body['shopperName'] = [
                'firstName' => $this->request->getShopperName()->getFirstName(),
                'lastName' => $this->request->getShopperName()->getLastName(),
            ];
        }

        if ($this->request->getInstallments() !== null) {
            $body['installments'] = [
                'plan' => $this->request->getInstallments()->getPlan(),
                'value' => $this->request->getInstallments()->getValue(),
            ];
        }

        if ($this->request->getCaptureDelayHours() >= 0) {
            $body['captureDelayHours'] = $this->request->getCaptureDelayHours();
        }

        if ($this->request->getLineItems() !== []) {
            $lineItems = [];

            foreach ($this->request->getLineItems() as $lineItem) {
                $lineItems[] = $this->getFormattedLineItem($lineItem);
            }

            $body['lineItems'] = $lineItems;
        }

        if ($this->request->getAdditionalData() && $this->request->getAdditionalData()->getRiskData()) {
            $basketItems = [];

            foreach ($this->request->getAdditionalData()->getRiskData()->getBasketItems() as $key => $basketItem) {
                $basketItems["item[$key]"] = $this->getFormattedBasketItem($basketItem);
            }

            $body['additionalData']['riskData']['basket'] = $basketItems;
        }

        if ($this->request->getAdditionalData() && $data = $this->request->getAdditionalData()->getEnhancedSchemeData()) {
            $itemDetailLine = [];

            foreach ($data->getItemDetailLines() as $key => $detailLine) {
                $itemDetailLine["itemDetailLine[$key]"] = $this->getFormattedItemDetailLine($detailLine);
            }

            $body['additionalData']['enhancedSchemeData'] = [
                'totalTaxAmount' => $data->getTotalTaxAmount(),
                'customerReference' => $data->getCustomerReference(),
                'freightAmount' => $data->getFreightAmount(),
                'shipFromPostalCode' => $data->getShipFromPostalCode(),
                'orderDate' => $data->getOrderDate(),
                'dutyAmount' => $data->getDutyAmount(),
                'destinationStateProvinceCode' => $data->getDestinationStateProvinceCode(),
                'destinationCountryCode' => $data->getDestinationCountryCode(),
                'destinationPostalCode' => $data->getDestinationPostalCode(),
                'itemDetailLine' => $itemDetailLine,
            ];
        }

        if ($this->request->getAdditionalData() && $this->request->getAdditionalData()->getManualCapture()) {
            $body['additionalData']['manualCapture'] = $this->request->getAdditionalData()->getManualCapture();
        }

        if (!empty($this->request->getDeviceFingerprint())) {
            $body['deviceFingerprint'] = $this->request->getDeviceFingerprint();
        }

        if (!empty($this->request->getBankAccount())) {
            $body['bankAccount'] = $this->request->getBankAccount();
        }

        return $body;
    }

    /** @noinspection NullPointerExceptionInspection */
    private function getFormattedBrowserInfo(): array
    {
        return  [
            'acceptHeader' => $this->request->getBrowserInfo()->getAcceptHeader(),
            'colorDepth' => $this->request->getBrowserInfo()->getColorDepth(),
            'javaEnabled' => $this->request->getBrowserInfo()->isJavaEnabled(),
            'language' => $this->request->getBrowserInfo()->getLanguage(),
            'screenHeight' => $this->request->getBrowserInfo()->getScreenHeight(),
            'screenWidth' => $this->request->getBrowserInfo()->getScreenWidth(),
            'timeZoneOffset' => $this->request->getBrowserInfo()->getTimeZoneOffset(),
            'userAgent' => $this->request->getBrowserInfo()->getUserAgent(),
        ];
    }

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

    private function getFormattedBasketItem(BasketItem $basketItem): array
    {
        return [
            'amountPerItem' => $basketItem->getAmountPerItem(),
            'brand' => $basketItem->getBrand(),
            'category' => $basketItem->getCategory(),
            'color' => $basketItem->getColor(),
            'currency' => $basketItem->getCurrency(),
            'itemID' => $basketItem->getItemId(),
            'manufacturer' => $basketItem->getManufacturer(),
            'productTitle' => $basketItem->getProductTitle(),
            'quantity' => $basketItem->getQuantity(),
            'receiverEmail' => $basketItem->getReceiverEmail(),
            'size' => $basketItem->getSize(),
            'sku' => $basketItem->getSku(),
            'upc' => $basketItem->getUpc(),
        ];
    }

    private function getFormattedItemDetailLine(ItemDetailLine $detailLine): array
    {
        return [
            'commodityCode' => $detailLine->getCommodityCode(),
            'description' => $detailLine->getDescription(),
            'discountAmount' => $detailLine->getDiscountAmount(),
            'productCode' => $detailLine->getProductCode(),
            'quantity' => $detailLine->getQuantity(),
            'totalAmount' => $detailLine->getTotalAmount(),
            'unitOfMeasure' => $detailLine->getUnitOfMeasure(),
            'unitPrice' => $detailLine->getUnitPrice(),
        ];
    }
}
