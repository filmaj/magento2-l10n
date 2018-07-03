<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PersonName;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class DataObjectProvider implements DataProviderInterface
{
    /**
     * @var DataObject
     */
    private $dataObject;

    /**
     * @var array
     */
    private $supportedWhiteList;

    public function __construct(DataObject $dataObject, array $supportedWhiteList = [])
    {
        $this->dataObject = $dataObject;
    }

    /**
     * @inheritDoc
     */
    public function supports(string $key): bool
    {
        if (!empty($this->supportedWhiteList) && !in_array($key, $this->supportedWhiteList)) {
            return false;
        }

        $dataExists = $this->dataObject->hasData($key);
        return $dataExists;
    }

    /**
     * @inheritDoc
     */
    public function contains(string $key): bool
    {
        if (!$this->supports($key)) {
            return false;
        }

        $data = (string)$this->dataObject->getDataByKey($key);
        return !empty($data);
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): string
    {
        if (!$this->contains($key)) {
            throw new LocalizedException(
                __('Name part "%1" is not specified.', $key)
            );
        }

        return (string)$this->dataObject->getDataByKey($key);
    }
}
