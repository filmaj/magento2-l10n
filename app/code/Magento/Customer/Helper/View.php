<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Helper;

use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Name\FromCustomerNameDataProviderFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\PersonName\Formatter;

/**
 * Customer helper for view.
 */
class View extends \Magento\Framework\App\Helper\AbstractHelper implements CustomerNameGenerationInterface
{
    /**
     * @deprecated
     * @var CustomerMetadataInterface
     */
    protected $_customerMetadataService;

    /**
     * @var FromCustomerNameDataProviderFactory
     */
    private $nameDataProviderFactory;

    /**
     * @var Formatter
     */
    private $nameFormatter;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param CustomerMetadataInterface $customerMetadataService
     * @param FromCustomerNameDataProviderFactory $nameDataProviderFactory,
     * @param Formatter $nameFormatter
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CustomerMetadataInterface $customerMetadataService,
        FromCustomerNameDataProviderFactory $nameDataProviderFactory = null,
        Formatter $nameFormatter = null
    ) {
        $this->_customerMetadataService = $customerMetadataService;
        parent::__construct($context);
        $this->nameDataProviderFactory = $nameDataProviderFactory
            ?: ObjectManager::getInstance()->get(FromCustomerNameDataProviderFactory::class);
        $this->nameFormatter = $nameFormatter
            ?: ObjectManager::getInstance()->get(Formatter::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerName(CustomerInterface $customerData)
    {
        $name = $this->nameFormatter->format(
            $this->nameDataProviderFactory->create($customerData),
            Formatter::FORMAT_LONG
        );
        return $name;
    }
}
