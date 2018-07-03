<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\PersonName;

use Magento\Framework\Exception\LocalizedException;

class FormatParser {

    const TYPE_NAME_PART = 'namePart';

    const TYPE_DELIMITER = 'delimiter';

    const MODIFIER_REQUIRED = 'r';

    const MODIFIER_BIND_PRECEDING_DELIMITER = 'b';

    public function parse(FormatInterface $format)
    {
        $template = $format->getTemplate();
        $parsed = [];

        $unprocessedPart = $template;
        while (!empty($unprocessedPart)) {
            list (
                $delimiter,
                $namePart,
                $unprocessedPart
            ) = $this->processNextPart($unprocessedPart);
            list($namePart, $options) = $this->parseNamePart($namePart);

            if ($delimiter !== '') {
                $node = [
                    'content' => [
                        'type' => 'static',
                        'value' => $delimiter,
                    ]
                ];
                if ($options['isPrecedingDelimiterBound']) {
                    if ($options['isOptional']) {
                        $node['dependsOn'] =  $namePart;
                    }
                } elseif (count($parsed) && isset($parsed[count($parsed) - 1]['dependsOn'])) {
                    $node['dependsOn'] = $parsed[count($parsed) - 1]['dependsOn'];
                };
                $parsed[] = $node;
            }
            if ($namePart !== '') {
                $node = [
                    'content' => [
                        'type' => 'dynamic',
                        'value' => $namePart,
                    ]
                ];
                if ($options['isOptional']) {
                    $node['dependsOn'] = $namePart;
                }
                $parsed[] = $node;
            }
        }

        return $parsed;
    }

    private function processNextPart(string $subject)
    {
        $namePartPosition = $this->findPositionsOfSequentialOpenAndCloseTags($subject);
        if ($namePartPosition === false) {
            $delimiter = $subject;
            $namePart = '';
            $unprocessedPart = '';
        } else {
            list($namePartBegin, $namePartEnd) = $namePartPosition;
            $delimiter = substr($subject, 0, $namePartBegin);
            $namePart = substr($subject, $namePartBegin + 1, $namePartEnd - $namePartBegin - 1);
            $unprocessedPart = substr($subject, min($namePartEnd + 1, strlen($subject)));
        }

        return [
            $delimiter,
            $namePart,
            $unprocessedPart
        ];
    }

    private function findPositionsOfSequentialOpenAndCloseTags(string $subject) {
        $openTag = '{';
        $closeTag = '}';

        $positionOfFirstOpenTag = strpos($subject, $openTag);
        if ($positionOfFirstOpenTag === false) {
            return false;
        }
        $positionOfCloseTagAfterOpenTag = strpos($subject, $closeTag, $positionOfFirstOpenTag);
        if ($positionOfCloseTagAfterOpenTag === false) {
            return false;
        }
        // we can be sure that position exists
        $positionOfOpenTagBeforeCloseTag = strrpos(
            $subject,
            $openTag,
            -(strlen($subject) - $positionOfCloseTagAfterOpenTag)
        );
        return [
            $positionOfOpenTagBeforeCloseTag,
            $positionOfCloseTagAfterOpenTag
        ];
    }

    private function parseNamePart(string $namePart): array
    {
        $parsed = explode('|', $namePart, 2);
        $namePartType = $parsed[0];
        $namePartOptions = $this->parseNamePartOptions(isset($parsed[1]) ? $parsed[1] : '');
        return [
            $namePartType,
            $namePartOptions
        ];
    }

    private function parseNamePartOptions(string $appliedModifiers)
    {
        $options = [
            'isOptional' => true,
            'isPrecedingDelimiterBound' => false,
        ];

        $modifiers = [
            self::MODIFIER_REQUIRED => [
                'isOptional' => false,
            ],
            self::MODIFIER_BIND_PRECEDING_DELIMITER => [
                'isPrecedingDelimiterBound' => true,
            ],
        ];

        foreach (array_filter(str_split($appliedModifiers)) as $modifier) {
            if (!isset($modifiers[$modifier])) {
                throw new LocalizedException(
                    __('Unknown name format modifier "%1".', $modifier)
                );
            }
            $options = array_merge($options, $modifiers[$modifier]);
        }

        return $options;
    }
}