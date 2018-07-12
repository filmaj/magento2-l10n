<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Address\Renderer;

use Magento\Customer\Model\Address\AddressModelInterface;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\PersonName\ArrayProviderFactory;
use Magento\Framework\PersonName\Formatter;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Address format renderer default
 */
class DefaultRenderer extends AbstractBlock implements RendererInterface
{
    /**
     * Format type object
     *
     * @var \Magento\Framework\DataObject
     */
    protected $_type;

    /**
     * @var ElementFactory
     */
    protected $_elementFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var \Magento\Customer\Api\AddressMetadataInterface
     */
    protected $_addressMetadataService;

    /**
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * @var ArrayProviderFactory
     */
    private $nameDataProviderFactory;

    /**
     * @var Formatter
     */
    private $nameFormatter;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param ElementFactory $elementFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Customer\Api\AddressMetadataInterface $metadataService
     * @param Mapper $addressMapper
     * @param array $data
     * @param ArrayProviderFactory $nameDataProviderFactory
     * @param Formatter $nameFormatter
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        ElementFactory $elementFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Customer\Api\AddressMetadataInterface $metadataService,
        Mapper $addressMapper,
        array $data = [],
        ArrayProviderFactory $nameDataProviderFactory = null,
        Formatter $nameFormatter = null
    ) {
        $this->_elementFactory = $elementFactory;
        $this->_countryFactory = $countryFactory;
        $this->_addressMetadataService = $metadataService;
        $this->addressMapper = $addressMapper;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->nameDataProviderFactory = $nameDataProviderFactory
            ?: ObjectManager::getInstance()->get(ArrayProviderFactory::class);
        $this->nameFormatter = $nameFormatter
            ?: ObjectManager::getInstance()->get(Formatter::class);
    }

    /**
     * Retrieve format type object
     *
     * @return \Magento\Framework\DataObject
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Retrieve format type object
     *
     * @param  \Magento\Framework\DataObject $type
     * @return $this
     */
    public function setType(\Magento\Framework\DataObject $type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * @param AddressModelInterface|null $address
     * @return string
     * All new code should use renderArray based on Metadata service
     */
    public function getFormat(AddressModelInterface $address = null)
    {
        $countryFormat = $address === null
        ? false : $address->getCountryModel()->getFormat(
            $this->getType()->getCode()
        );
        $format = $countryFormat ? $countryFormat->getFormat() : $this->getType()->getDefaultFormat();
        return $format;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function render(AddressModelInterface $address, $format = null)
    {
        $address = $address->getDataModel(0, 0);
        return $this->renderArray($this->addressMapper->toFlatArray($address), $format);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatArray($addressAttributes = null)
    {
        $countryFormat = false;
        if ($addressAttributes && isset($addressAttributes['country_id'])) {
            /** @var \Magento\Directory\Model\Country $country */
            $country = $this->_countryFactory->create()->load($addressAttributes['country_id']);
            $countryFormat = $country->getFormat($this->getType()->getCode());
        }
        $format = $countryFormat ? $countryFormat->getFormat() : $this->getType()->getDefaultFormat();
        return $format;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function renderArray($addressAttributes, $format = null)
    {
        switch ($this->getType()->getCode()) {
            case 'html':
                $dataFormat = ElementFactory::OUTPUT_FORMAT_HTML;
                break;
            case 'pdf':
                $dataFormat = ElementFactory::OUTPUT_FORMAT_PDF;
                break;
            case 'oneline':
                $dataFormat = ElementFactory::OUTPUT_FORMAT_ONELINE;
                break;
            default:
                $dataFormat = ElementFactory::OUTPUT_FORMAT_TEXT;
                break;
        }

        $attributesMetadata = $this->_addressMetadataService->getAllAttributesMetadata();
        $data = [];
        foreach ($attributesMetadata as $attributeMetadata) {
            if (!$attributeMetadata->isVisible()) {
                continue;
            }
            $attributeCode = $attributeMetadata->getAttributeCode();
            if ($attributeCode == 'country_id' && isset($addressAttributes['country_id'])) {
                $data['country'] = $this->_countryFactory->create()->loadByCode(
                    $addressAttributes['country_id']
                )->getName();
            } elseif ($attributeCode == 'region' && isset($addressAttributes['region'])) {
                $data['region'] = __($addressAttributes['region']);
            } elseif (isset($addressAttributes[$attributeCode])) {
                $value = $addressAttributes[$attributeCode];
                $dataModel = $this->_elementFactory->create(
                    $attributeMetadata,
                    $value,
                    'customer_address'
                );
                $value = $dataModel->outputValue($dataFormat);
                if ($attributeMetadata->getFrontendInput() == 'multiline') {
                    $values = $dataModel->outputValue(ElementFactory::OUTPUT_FORMAT_ARRAY);
                    // explode lines
                    foreach ($values as $k => $v) {
                        $key = sprintf('%s%d', $attributeCode, $k + 1);
                        $data[$key] = $v;
                    }
                }
                $data[$attributeCode] = $value;
            }
        }
        if ($this->getType()->getEscapeHtml()) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->escapeHtml($value);
            }
        }
        $format = $format !== null ? $format : $this->getFormatArray($addressAttributes);

        $templateFilter = $this->filterManager->get('template', ['variables' => $data]);
        $templateFilter->setTemplateProcessor(function (...$args) {
            return $this->fullNameTemplateProcessor(...$args);
        });
        $formatted = $templateFilter->filter($format);
        return $formatted;
    }

    private function fullNameTemplateProcessor($configPath, array $vars)
    {
        $config = explode('/', $configPath, 2);
        if ($config[0] !== self::FULL_NAME_PLACEHOLDER) {
            return '{Error in template processing}';
        }

        $fullNameFormat = isset($config[1]) ? $config[1] : Formatter::FORMAT_DEFAULT;

        $fullName = $this->nameFormatter->format(
            $this->nameDataProviderFactory->create([
                'data' => $vars
            ]),
            $fullNameFormat
        );

        return $fullName;
    }
}
