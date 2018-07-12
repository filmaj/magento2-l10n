<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\PersonName;

use Magento\Framework\DataObject;

/**
 * Service class to render person name in defined format.
 *
 * @api
 */
class DataObjectFormatter
{
    /**
     * @var FromDataObjectDataProviderFactory
     */
    private $factory;

    /**
     * @var Formatter
     */
    private $formatter;

    public function __construct(
        FromDataObjectDataProviderFactory $factory,
        Formatter $formatter
    ) {
        $this->factory = $factory;
        $this->formatter = $formatter;
    }

    public function format(DataObject $data, string $format): string
    {
        $provider = $this->factory->create($data);
        $formatted = $this->formatter->format($provider, $format);
        return $formatted;
    }

}