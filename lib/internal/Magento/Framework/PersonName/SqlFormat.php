<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\PersonName;

class SqlFormat
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

    public function getSqlParts(array $fieldsMap): array
    {
        $nameSqlParts = [];
        $parts = $this->getParsedFormat();
        foreach ($parts as $part) {
            $nameSqlParts[] = $this->convertParsedPartToSql($part, $fieldsMap);
        }
        $nameSqlParts = array_filter($nameSqlParts);

        return $nameSqlParts;
    }

    private function getParsedFormat(): array
    {
        $parsedFormat = $this->parser->parse($this->format);
        return $parsedFormat;
    }

    private function convertParsedPartToSql(array $partMetadata, array $fieldsMap): string
    {
        if (!isset($partMetadata['content']['type'], $partMetadata['content']['value'])) {
            throw new \InvalidArgumentException('Content type and value are required.');
        }
        if (isset($partMetadata['dependsOn']) && !isset($fieldsMap[$partMetadata['dependsOn']])) {
            return '';
        }

        switch ($partMetadata['content']['type']) {
            case 'static':
                $sqlValue = sprintf(
                    "'%s'",
                    addslashes($partMetadata['content']['value'])
                );
                break;
            case 'dynamic':
                $sqlValue = sprintf(
                    'TRIM(%s)',
                    $this->resolveFieldName($partMetadata['content']['value'], $fieldsMap)
                );
                break;
            default:
                $errorMessage = sprintf(
                    'Unknown content type "%s".',
                    $partMetadata['content']['type']
                );
                throw new \InvalidArgumentException($errorMessage);
        }

        if (isset($partMetadata['dependsOn'])) {
            $sqlValue = sprintf(
                "IF(TRIM(%s) != '', %s, '')",
                $this->resolveFieldName($partMetadata['dependsOn'], $fieldsMap),
                $sqlValue
            );
        }

        return $sqlValue;
    }

    private function resolveFieldName(string $field, array $map): string
    {
        if (!isset($map[$field])) {
            throw new \InvalidArgumentException(sprintf(
                'Field map for name part "%s" is not provided.',
                $field
            ));
        }
        return $map[$field];
    }
}
