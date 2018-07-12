<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\PersonName;

class NamePartsOrder
{
    private $format;

    private $parser;

    private $orderedNameParts;

    public function __construct(
        FormatInterface $format,
        FormatParser $parser
    ) {
        $this->format = $format;
        $this->parser = $parser;
    }

    public function getOrderedNameParts(): array
    {
        if ($this->orderedNameParts === null) {
            $parsed = $this->parser->parse($this->format);
            $dynamicParts = array_filter($parsed, function ($parsedPart) {
                return $parsedPart['content']['type'] === 'dynamic';
            });
            $dynamicFields = array_map(function ($parsedPart) {
                return $parsedPart['content']['value'];
            }, $dynamicParts);
            $this->orderedNameParts = array_values(array_unique($dynamicFields));
        }
        return $this->orderedNameParts;
    }

    public function getNamePartOrder(string $namePart): int
    {
        $index = array_search($namePart, $this->getOrderedNameParts());
        if (false === $index) {
            throw new \InvalidArgumentException(sprintf(
                'Name part "%s" is not used in format.',
                $namePart
            ));
        }
    }

    public function hasNamePart(string $namePart): bool
    {
        $nameParts = $this->getOrderedNameParts();
        return in_array($namePart, $nameParts);
    }
}
