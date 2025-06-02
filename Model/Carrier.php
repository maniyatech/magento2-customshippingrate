<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_CustomShippingRate
 */

namespace ManiyaTech\CustomShippingRate\Model;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Helper\Carrier as ShippingCarrierHelper;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use ManiyaTech\CustomShippingRate\ViewModel\CustomShippingRate;
use Psr\Log\LoggerInterface;

class Carrier extends AbstractCarrier implements CarrierInterface
{
    /**
     * Code of the carrier
     *
     * @var string
     */
    public const CODE = 'customshippingrate';

    /**
     * Code of the carrier
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     *
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     *
     * @var ShippingCarrierHelper
     */
    protected $_carrierHelper;

    /**
     * @var CollectionFactory
     */
    protected $_rateFactory;

    /**
     * @var State
     */
    protected $_state;

    /**
     * @var CustomShippingRate
     */
    protected $customShippingRateViewModel;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateFactory
     * @param ShippingCarrierHelper $carrierHelper
     * @param MethodFactory $rateMethodFactory
     * @param State $state
     * @param CustomShippingRate $customShippingRateViewModel
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateFactory,
        ShippingCarrierHelper $carrierHelper,
        MethodFactory $rateMethodFactory,
        State $state,
        CustomShippingRate $customShippingRateViewModel,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->_scopeConfig = $scopeConfig;
        $this->_rateErrorFactory = $rateErrorFactory;
        $this->_logger = $logger;
        $this->_rateFactory = $rateFactory;
        $this->_carrierHelper = $carrierHelper;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_state = $state;
        $this->customShippingRateViewModel = $customShippingRateViewModel;
    }

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return Collection|Result
     * @throws LocalizedException
     */
    public function collectRates(RateRequest $request)
    {
        $result = $this->_rateFactory->create();

        if (!$this->getConfigFlag('active') || (!$this->isAdmin() && $this->hideShippingMethodOnFrontend())) {
            return $result;
        }

        foreach ($this->customShippingRateViewModel->getShippingType($request->getStoreId()) as $shippingType) {
            $rate = $this->_rateMethodFactory->create();
            $rate->setCarrier($this->_code);
            $rate->setCarrierTitle($this->getConfigData('title'));
            $rate->setMethod($shippingType['code']);
            $rate->setMethodTitle($shippingType['title']);
            $rate->setCost($shippingType['price']);
            $rate->setPrice($shippingType['price']);

            $result->append($rate);
        }

        return $result;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $result = [];
        foreach ($this->customShippingRateViewModel->getShippingType() as $shippingType) {
            $result[$shippingType['code']] = $shippingType['title'];
        }
        return $result;
    }

    /**
     * Tracking Available
     *
     * @return bool
     */
    public function isTrackingAvailable()
    {
        return false;
    }

    /**
     * Check Shipping Method Show on frontend
     *
     * @return bool
     * @throws LocalizedException
     */
    protected function hideShippingMethodOnFrontend()
    {
        return !$this->getConfigFlag('show_on_frontend');
    }

    /**
     * Check enable/disable method on frontend
     *
     * @return bool
     * @throws LocalizedException
     */
    protected function isAdmin()
    {
        return $this->_state->getAreaCode() == FrontNameResolver::AREA_CODE;
    }
}
