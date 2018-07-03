<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\PersonName;

/**
 * Service class to render person name in defined format.
 *
 * @api
 */
class Formatter
{
    const FORMAT_DEFAULT = 'default';
    const FORMAT_LONG = 'long';

    private const REQUIRED_FORMATS = [
        self::FORMAT_DEFAULT,
        self::FORMAT_LONG,
    ];

    /**
     * @var RendererInterface[]
     */
    private $renderers;

    /**
     * Formatter constructor.
     * @param RendererInterface[] $renderers
     */
    public function __construct(array $renderers)
    {
        $this->renderers = [];
        foreach ($renderers as $format => $renderer) {
            $this->registerRenderer($format, $renderer);
        }
        $this->assertRequiredFormatsDefined();
    }

    private function registerRenderer(string $format, RendererInterface $renderer): void
    {
        $this->renderers[$format] = $renderer;
    }

    private function assertRequiredFormatsDefined()
    {
        $missedRequiredFormats = array_diff(array_keys($this->renderers), self::REQUIRED_FORMATS);
        if (!empty($missedRequiredFormats)) {
            throw new \InvalidArgumentException(sprintf(
                'Format(s) %s are required but not configured.',
                join(', ', $missedRequiredFormats)
            ));
        }
    }

    public function format(DataProviderInterface $data, string $format): string
    {
        if (!isset($this->renderers[$format])) {
            throw new \InvalidArgumentException(sprintf('Name format "%s" is not configured.', $format));
        }

        $formatted = $this->renderers[$format]->render($data);
        return $formatted;
    }
}