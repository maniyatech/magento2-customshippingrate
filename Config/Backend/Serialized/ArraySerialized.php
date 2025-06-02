<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_CustomShippingRate
 */

namespace ManiyaTech\CustomShippingRate\Config\Backend\Serialized;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use ManiyaTech\CustomShippingRate\ViewModel\CustomShippingRate;

class ArraySerialized extends \Magento\Config\Model\Config\Backend\Serialized\ArraySerialized
{
    /**
     * @var CustomShippingRate
     */
    protected $customShippingRateViewModel;

    /**
     * ArraySerialized constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param CustomShippingRate $customShippingRateViewModel
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        CustomShippingRate $customShippingRateViewModel,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );

        $this->customShippingRateViewModel = $customShippingRateViewModel;
    }

    /**
     * Set Custom Shipping Method
     *
     * @return void
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        $this->setValue($this->customShippingRateViewModel->shippingArrayObject($this->getValue()));
    }
}
