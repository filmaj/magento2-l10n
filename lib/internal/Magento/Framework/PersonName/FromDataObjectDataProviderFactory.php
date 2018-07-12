<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\PersonName;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;

class FromDataObjectDataProviderFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(DataObject $dataObject): DataProviderInterface
    {
        $data = $dataObject->toArray();
        $provider = $this->objectManager->create(
            ArrayProvider::class,
            [
                'data' => $data,
            ]
        );
        return $provider;
    }
}