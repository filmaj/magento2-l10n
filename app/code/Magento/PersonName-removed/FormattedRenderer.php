<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PersonName;

class FormattedRenderer implements RendererInterface
{
    /**
     * @var FormatInterface
     */
    private $format;

    /**
     * @var FormatParser
     */
    private $parser;

    public function __construct(
        FormatInterface $format,
        FormatParser $parser
    ) {
        $this->format = $format;
        $this->parser = $parser;
    }

    public function render(DataProviderInterface $data): string
    {
        $rendered = '';
        $parts = $this->getParsedFormat();
        foreach ($parts as $part) {
            $rendered .= $this->renderPart($part, $data);
        }
        return $rendered;
    }

    private function getParsedFormat(): array
    {
        $parsedFormat = $this->parser->parse($this->format);
        return $parsedFormat;
    }

    private function renderPart(array $partMetadata, $data): string
    {
        if (!isset($partMetadata['content']['type'], $partMetadata['content']['value'])) {
            throw new \InvalidArgumentException('Content type and value are required.');
        }

        if (isset($partMetadata['dependsOn']) && !$data->contains($partMetadata['dependsOn'])) {
            return '';
        }

        switch ($partMetadata['content']['type']) {
            case 'static':
                return $partMetadata['content']['value'];
            case 'dynamic':
                return $data->get($partMetadata['content']['value']);
            default:
                $errorMessage = sprintf(
                    'Unknown content type "%s".',
                    $partMetadata['content']['type']
                );
                throw new \InvalidArgumentException($errorMessage);
        }
    }
}
