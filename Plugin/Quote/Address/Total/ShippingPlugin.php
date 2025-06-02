<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_CustomShippingRate
 */

namespace ManiyaTech\CustomShippingRate\Plugin\Quote\Address\Total;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\Shipping;
use ManiyaTech\CustomShippingRate\ViewModel\CustomShippingRate;
use ManiyaTech\CustomShippingRate\Model\Carrier;

class ShippingPlugin
{
    /**
     * @var CustomShippingRate
     */
    protected $customShippingRateViewModel;

    /**
     * ShippingPlugin constructor.
     *
     * @param CustomShippingRate $customShippingRateViewModel
     */
    public function __construct(
        CustomShippingRate $customShippingRateViewModel
    ) {
        $this->customShippingRateViewModel = $customShippingRateViewModel;
    }

    /**
     * Collect rate.
     *
     * @param Shipping $subject
     * @param callable $proceed
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return mixed
     */
    public function aroundCollect(
        Shipping $subject,
        callable $proceed,
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        $shipping = $shippingAssignment->getShipping();
        $address = $shipping->getAddress();
        $method = $address->getShippingMethod();
        $storeId = $quote->getStoreId();

        if (!$this->customShippingRateViewModel->isEnabled($storeId)
            || $address->getAddressType() != Address::ADDRESS_TYPE_SHIPPING
            || strpos((string) $method, Carrier::CODE) === false
        ) {
            return $proceed($quote, $shippingAssignment, $total);
        }

        $customShippingOption = $this->getCustomShippingJsonToArray($method, $address, $storeId);

        if ($customShippingOption && strpos((string) $method, $customShippingOption['code']) !== false) {
            //update shipping code
            $shipping->setMethod($customShippingOption['code']);
            $address->setShippingMethod($customShippingOption['code']);
            $this->updateCustomRate($address, $customShippingOption);
        }

        return $proceed($quote, $shippingAssignment, $total);
    }

    /**
     * Update rate
     *
     * @param object $address
     * @param array $customShippingOption
     */
    protected function updateCustomRate($address, $customShippingOption)
    {
        if ($selectedRate = $this->getSelectedShippingRate($address, $customShippingOption['code'])) {
            $cost = (float) $customShippingOption['rate'];
            $description = trim($customShippingOption['description']);

            $selectedRate->setPrice($cost);
            $selectedRate->setCost($cost);
            //Empty by default. Use in third-party modules
            if (!empty($description) || strlen($description) > 2) {
                $selectedRate->setMethodTitle($description);
            }
        }
    }

    /**
     * Get Custom Shipping rate json array
     *
     * @param json $json
     * @param object $address
     * @param int $storeId
     * @return array|bool
     */
    private function getCustomShippingJsonToArray($json, $address, $storeId = null)
    {
        $isJson = $this->customShippingRateViewModel->isJson($json);

        //reload exist shipping cost if custom shipping method
        if ($json && !$isJson) {
            $rate = 0;
            if ($selectedRate = $this->getSelectedShippingRate($address, $json)) {
                $rate = $selectedRate->getPrice();
            }

            $jsonToArray = [
                'code' => $json,
                'type' => $this->customShippingRateViewModel->getShippingCodeFromMethod($json, $storeId),
                'rate' => $rate
            ];

            return $this->formatShippingArray($jsonToArray);
        }

        $jsonToArray = (array)json_decode($json, true);

        if (is_array($jsonToArray) && count($jsonToArray) == 4) {
            return $this->formatShippingArray($jsonToArray);
        }

        return false;
    }

    /**
     * Get Selected Shipping Rate
     *
     * @param object $address
     * @param string $code
     * @return null | Rate
     */
    protected function getSelectedShippingRate($address, $code)
    {
        $selectedRate = null;

        if ($code) {
            foreach ($address->getAllShippingRates() as $rate) {
                if ($rate->getCode() == $code) {
                    $selectedRate = $rate;
                    break;
                }
            }
        }

        return $selectedRate;
    }

    /**
     * Format Shipping Array
     *
     * @param array $jsonToArray
     * @return array
     */
    protected function formatShippingArray($jsonToArray)
    {
        $customShippingOption = [
            'code' => '',
            'rate' => 0,
            'type' => '',
            'description' => ''
        ];

        foreach ((array) $jsonToArray as $key => $value) {
            $customShippingOption[$key] = $value;
        }

        return $customShippingOption;
    }
}
