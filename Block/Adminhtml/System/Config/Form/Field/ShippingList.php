<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_CustomShippingRate
 */

namespace ManiyaTech\CustomShippingRate\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use ManiyaTech\CustomShippingRate\ViewModel\CustomShippingRate;

class ShippingList extends AbstractFieldArray
{
    /**
     * @var CustomShippingRate
     */
    protected $customShippingRateViewModel;

    /**
     * ShippingList constructor.
     * @param Context $context
     * @param CustomShippingRate $customShippingRateViewModel
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomShippingRate $customShippingRateViewModel,
        array $data = []
    ) {
        $this->customShippingRateViewModel = $customShippingRateViewModel;
        parent::__construct($context, $data);
    }

    /**
     * Initialise columns for 'Store Locations'
     * Label is name of field
     * Class is storefront validation action for field
     *
     * @return void
     */
    protected function _construct()
    {
        foreach ($this->customShippingRateViewModel->getHeaderColumns() as $key => $column) {
            $this->addColumn(
                $key,
                [
                    'label' => __($column['label']),
                    'class' => $column['class']
                ]
            );
        }

        $this->_addAfter = false;
        parent::_construct();
    }
}
