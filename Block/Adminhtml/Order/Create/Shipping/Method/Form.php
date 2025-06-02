<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_CustomShippingRate
 */

namespace ManiyaTech\CustomShippingRate\Block\Adminhtml\Order\Create\Shipping\Method;

use Magento\Quote\Model\Quote\Address\Rate;
use ManiyaTech\CustomShippingRate\Model\Carrier;

class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form
{
    /** @var Rate|false **/
    protected $activeMethodRate;

    /**
     * Custom shipping rate
     *
     * @return string
     */
    public function getActiveCustomShippingRateMethod()
    {
        $rate = $this->getActiveMethodRate();
        return $rate && $rate->getCarrier() == Carrier::CODE ? $rate->getMethod() : '';
    }

    /**
     * Custom shipping rate
     *
     * @return string
     */
    public function getActiveCustomShippingRatePrice()
    {
        $rate = $this->getActiveMethodRate();
        return $this->getActiveCustomShippingRateMethod() && $rate->getPrice() ? $rate->getPrice() * 1 : '';
    }

    /**
     * Custom shipping rate
     *
     * @return string
     */
    public function isCustomShippingRateActive()
    {
        if (empty($this->activeMethodRate)) {
            $this->activeMethodRate = $this->getActiveMethodRate();
        }

        return $this->activeMethodRate && $this->activeMethodRate->getCarrier() == Carrier::CODE ? true : false;
    }

    /**
     * Retrieve array of shipping rates groups
     *
     * @return array
     */
    public function getGroupShippingRates()
    {
        $rates = $this->getShippingRates();

        if (array_key_exists(Carrier::CODE, $rates)) {
            if (!$this->isCustomShippingRateActive()) {
                unset($rates[Carrier::CODE]);
            } else {
                $activeRateMethod = $this->getActiveCustomShippingRateMethod();
                foreach ($rates[Carrier::CODE] as $key => $rate) {
                    if ($rate->getMethod() != $activeRateMethod) {
                        unset($rates[Carrier::CODE][$key]);
                    }
                }
            }
        }

        return $rates;
    }
}
