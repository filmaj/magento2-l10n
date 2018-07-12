<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Name;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\PersonName\ArrayProvider;
use Magento\Framework\PersonName\DataProviderInterface;
use Magento\Framework\PersonName\FormatInterface;

class FromCustomerNameDataProviderFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CustomerMetadataInterface
     */
    private $customerMetadata;

    public function __construct(
        ObjectManagerInterface $objectManager,
        CustomerMetadataInterface $customerMetadata
    ) {
        $this->objectManager = $objectManager;
        $this->customerMetadata = $customerMetadata;
    }

    public function create(CustomerInterface $customer): DataProviderInterface
    {
        $data = $this->extractData($customer);
        $accessibleData = $this->getAccessibleDataList();

        $provider = $this->objectManager->create(
            ArrayProvider::class,
            [
                'data' => $data,
                'supportedWhiteList' => $accessibleData
            ]
        );
        return $provider;
    }

    private function extractData(CustomerInterface $customer): array
    {
        $data = [
            FormatInterface::PART_NAME_PREFIX => $customer->getPrefix(),
            FormatInterface::PART_GIVEN_NAME => $customer->getFirstname(),
            FormatInterface::PART_MIDDLE_NAME => $customer->getMiddlename(),
            FormatInterface::PART_FAMILY_NAME => $customer->getLastname(),
            FormatInterface::PART_NAME_SUFFIX => $customer->getSuffix(),
        ];
        return $data;
    }

    private function getAccessibleDataList()
    {
        $accessibleData = [
            FormatInterface::PART_GIVEN_NAME,
            FormatInterface::PART_FAMILY_NAME,
        ];
        if ($this->isOptionalPartVisible(CustomerInterface::PREFIX)) {
            $accessibleData[] = FormatInterface::PART_NAME_PREFIX;
        }
        if ($this->isOptionalPartVisible(CustomerInterface::MIDDLENAME)) {
            $accessibleData[] = FormatInterface::PART_MIDDLE_NAME;
        }
        if ($this->isOptionalPartVisible(CustomerInterface::SUFFIX)) {
            $accessibleData[] = FormatInterface::PART_NAME_SUFFIX;
        }
        return $accessibleData;
    }

    private function isOptionalPartVisible(string $part): bool
    {
        $partMetadata = $this->customerMetadata->getAttributeMetadata($part);
        return $partMetadata->isVisible();
    }
}
