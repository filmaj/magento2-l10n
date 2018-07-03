<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PersonName;

use Magento\Framework\Exception\LocalizedException;

interface DataProviderInterface
{
    /**
     * Check if data provider may contain requested field
     *
     * @param string $key
     * @return bool
     */
    public function supports(string $key): bool;

    /**
     * Check if not empty string for key may be returned
     *
     * @param string $key
     * @return bool
     */
    public function contains(string $key): bool;

    /**
     * Provide not empty value for key
     *
     * @param string $key
     * @return string
     * @throws LocalizedException if data for key not supported or empty
     */
    public function get(string $key): string;
}