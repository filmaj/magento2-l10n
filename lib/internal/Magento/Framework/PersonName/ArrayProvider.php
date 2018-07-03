<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\PersonName;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class ArrayProvider implements DataProviderInterface
{
    /**
     * @var DataObject
     */
    private $data;

    /**
     * @var array
     */
    private $supportedWhiteList;

    public function __construct(array $data, array $supportedWhiteList = [])
    {
        $this->data = $data;
        $this->supportedWhiteList = $supportedWhiteList;
    }

    /**
     * @inheritDoc
     */
    public function supports(string $key): bool
    {
        if (!empty($this->supportedWhiteList) && !in_array($key, $this->supportedWhiteList)) {
            return false;
        }

        $dataExists = array_key_exists($key, $this->data);
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

        $data = (string)$this->data[$key];
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

        return (string)$this->data[$key];
    }
}
