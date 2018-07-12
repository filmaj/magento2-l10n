<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Name;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\PersonName\ArrayProvider;
use Magento\Framework\PersonName\DataProviderInterface;
use Magento\Framework\PersonName\FormatInterface;

class FromAddressNameDataProviderFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var AddressMetadataInterface
     */
    private $addressMetadata;

    public function __construct(
        ObjectManagerInterface $objectManager,
        AddressMetadataInterface $addressMetadata
    ) {
        $this->objectManager = $objectManager;
        $this->addressMetadata = $addressMetadata;
    }

    public function create(AddressInterface $address): DataProviderInterface
    {
        $data = $this->extractData($address);
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

    private function extractData(AddressInterface $address): array
    {
        $data = [
            FormatInterface::PART_NAME_PREFIX => $address->getPrefix(),
            FormatInterface::PART_GIVEN_NAME => $address->getFirstname(),
            FormatInterface::PART_MIDDLE_NAME => $address->getMiddlename(),
            FormatInterface::PART_FAMILY_NAME => $address->getLastname(),
            FormatInterface::PART_NAME_SUFFIX => $address->getSuffix(),
        ];
        return $data;
    }

    private function getAccessibleDataList()
    {
        $accessibleData = [
            FormatInterface::PART_GIVEN_NAME,
            FormatInterface::PART_FAMILY_NAME,
        ];
        if ($this->isOptionalPartVisible(AddressInterface::PREFIX)) {
            $accessibleData[] = FormatInterface::PART_NAME_PREFIX;
        }
        if ($this->isOptionalPartVisible(AddressInterface::MIDDLENAME)) {
            $accessibleData[] = FormatInterface::PART_MIDDLE_NAME;
        }
        if ($this->isOptionalPartVisible(AddressInterface::SUFFIX)) {
            $accessibleData[] = FormatInterface::PART_NAME_SUFFIX;
        }
        return $accessibleData;
    }

    private function isOptionalPartVisible(string $part): bool
    {
        $partMetadata = $this->addressMetadata->getAttributeMetadata($part);
        return $partMetadata->isVisible();
    }
}
