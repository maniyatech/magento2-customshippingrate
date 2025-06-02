<?php

namespace ManiyaTech\CustomShippingRate\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use ManiyaTech\CustomShippingRate\Model\Carrier;
use Magento\Framework\Serialize\SerializerInterface;

class CustomShippingRate implements ArgumentInterface
{
    /**
     * @var array
     */
    protected $shippingType;

    /**
     * @var array
     */
    protected $codes = [
        'code' => [
            'label' => 'Code',
            'class' => 'validate-no-empty validate-data',
            'default' => ''
        ],
        'title' => [
            'label' => 'Title',
            'class' => 'validate-no-empty',
            'default' => ''
        ],
        'price' => [
            'label' => 'Price',
            'class' => 'validate-no-empty greater-than-equals-to-0',
            'default' => ''
        ],
        'sort_order' => [
            'label' => 'Admin Sort',
            'class' => 'validate-no-empty greater-than-equals-to-0',
            'default' => 99
        ]
    ];

    /**
     * @var array
     */
    protected $headerTemplate;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
    }

    /**
     * Get Shipping Type
     *
     * @param int|null $storeId
     * @return array|mixed
     */
    public function getShippingType($storeId = null)
    {
        if (!$this->shippingType) {
            $arrayValues = [];
            $configData = $this->getConfigData('shipping_type', $storeId);

            if (is_string($configData) && !empty($configData) && $configData !== '[]') {
                if ($this->isJson($configData)) {
                    $arrayValues = (array) json_decode($configData, true);
                } else {
                    try {
                        $arrayValues = (array) array_values($this->serializer->unserialize($configData));
                    } catch (\InvalidArgumentException $e) {
                        $arrayValues = [];
                    }
                }
            }

            $arrayValues = $this->shippingArrayObject($arrayValues);

            usort($arrayValues, function ($a, $b) {
                if (array_key_exists('sort_order', $a)) {
                    return $a['sort_order'] - $b['sort_order'];
                } else {
                    return 0;
                }
            });

            $this->shippingType = $arrayValues;
        }

        return $this->shippingType;
    }

    /**
     * Extract method code from full method identifier
     *
     * @param string $method_code
     * @param string|null $storeId
     * @return string
     */
    public function getShippingCodeFromMethod($method_code, $storeId = null)
    {
        $result = '';

        foreach ($this->getShippingType($storeId) as $shippingType) {
            if (Carrier::CODE . '_' . $shippingType['code'] == $method_code) {
                $result = $shippingType['code'];
                break;
            }
        }

        return $result;
    }

    /**
     * Check if shipping method is enabled
     *
     * @param string|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'carriers/' . Carrier::CODE . '/active',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve information from carrier configuration
     *
     * @param string $field
     * @param string|null $storeId
     * @return string|null
     */
    public function getConfigData($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            'carriers/' . Carrier::CODE . '/' . $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if shipping is allowed in the selected country
     *
     * @param string|null $shippingCountry
     * @param int|null $storeId
     * @return bool
     */
    public function isVisibleShippingInSelectedCountry($shippingCountry = null, $storeId = null)
    {
        if (!$shippingCountry) {
            return false;
        }

        $sallowspecific = $this->scopeConfig->getValue(
            'carriers/' . Carrier::CODE . '/sallowspecific',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($sallowspecific == '1') {
            $specificCountries = $this->scopeConfig->getValue(
                'carriers/' . Carrier::CODE . '/specificcountry',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $allowedCountries = is_array($specificCountries)
                ? $specificCountries
                : explode(',', (string)$specificCountries);

            return in_array($shippingCountry, $allowedCountries);
        }

        return true;
    }

    /**
     * Determine if the given string is valid JSON
     *
     * @param string $string
     * @return bool
     */
    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Get header template
     *
     * @return array
     */
    public function getHeaderTemplate()
    {
        if (!$this->headerTemplate) {
            $this->headerTemplate = [];

            foreach ($this->getHeaderColumns() as $key => $column) {
                $this->headerTemplate[$key] = $column['default'];
            }
        }

        return $this->headerTemplate;
    }

    /**
     * Get header columns definition
     *
     * @return array
     */
    public function getHeaderColumns()
    {
        return $this->codes;
    }

    /**
     * Normalize shipping array with default fields
     *
     * @param array $values
     * @return array
     */
    public function shippingArrayObject($values)
    {
        $requiredFields = $this->getHeaderTemplate();

        if (is_array($values)) {
            foreach ($values as &$row) {
                $row = $row + $requiredFields;
            }
        }

        return $values;
    }
}
