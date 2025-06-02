<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_CustomShippingRate
 */

namespace ManiyaTech\CustomShippingRate\Plugin\Model\Quote;

use Magento\Quote\Api\Data\AddressInterface;

class AddressPlugin
{
    /**
     * Get Shipping Rates Collection
     *
     * @param AddressInterface $subject
     * @param callable $proceed
     * @return mixed
     */
    public function aroundCollectShippingRates(AddressInterface $subject, callable $proceed)
    {
        $found = false;
        $price = null;
        $description = null;

        //get custom shipping rate set by admin
        foreach ($subject->getAllShippingRates() as $rate) {
            if ($rate->getCode() == $subject->getShippingMethod()) {
                $found = true;
                $price = $rate->getPrice();
                $description = $rate->getMethodTitle();
                break;
            }
        }

        $return = $proceed();

        if ($found) {
            //reset custom shipping rate
            foreach ($subject->getAllShippingRates() as $rate) {
                if ($rate->getCode() == $subject->getShippingMethod()) {
                    $rate->setPrice($price);
                    $rate->setCost($price);
                    $rate->setMethodTitle($description);
                    break;
                }
            }
        }

        return $return;
    }
}
