<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\PersonName;

use Magento\Framework\Filter\Template;

class FormatParser
{
    public function parse(FormatInterface $format)
    {
        $template = $format->getTemplate();

        $dependentParts = $this->splitOnDependentParts($template);
        $parsedParts = $this->parseParts($dependentParts);

        return $parsedParts;
    }

    private function splitOnDependentParts(string $template): array
    {
        $parts = [];
        preg_match_all(
            Template::CONSTRUCTION_DEPEND_PATTERN,
            $template,
            $dependMatches,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        );

        $currentPosition = 0;
        foreach ($dependMatches as $dependingPart) {
            $dependingTemplate = $dependingPart[0][0];
            $dependingPartBeginPosition = $dependingPart[0][1];
            $dependsOn = $dependingPart[1][0];
            $dependingContent = $dependingPart[2][0];

            if ($currentPosition !== $dependingPartBeginPosition) {
                $parts[] = [
                    'template' => substr(
                        $template,
                        $currentPosition,
                        $dependingPartBeginPosition - $currentPosition
                    ),
                ];
            }

            $parts[] = [
                'template' => $dependingContent,
                'dependsOn' => $dependsOn,
            ];

            $currentPosition = $dependingPartBeginPosition + strlen($dependingTemplate);
        }

        if ($currentPosition < strlen($template)) {
            $parts[] = [
                'template' => substr(
                    $template,
                    $currentPosition
                ),
            ];
        }

        return $parts;
    }

    private function parseParts(array $templateParts): array
    {
        $parsedParts = [];
        foreach ($templateParts as $templatePart) {
            $parsedPartsFromTemplate = $this->parsePart($templatePart['template']);
            foreach ($parsedPartsFromTemplate as $part) {
                $parsedPart = [
                    'content' => $part,
                ];
                if (isset($templatePart['dependsOn'])) {
                    $parsedPart['dependsOn'] = $templatePart['dependsOn'];
                }
                $parsedParts[] = $parsedPart;
            }
        }
        return $parsedParts;
    }

    private function parsePart(string $template)
    {
        $parts = [];

        preg_match_all(
            '/{{var\s*(.*?)}}/si',
            $template,
            $matches,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        );

        $currentPosition = 0;
        foreach ($matches as $dynamicPart) {
            $dynamicPartPlaceholder = $dynamicPart[0][0];
            $dynamicPartBeginPosition = $dynamicPart[0][1];
            $dynamicPartSource = $dynamicPart[1][0];

            if ($currentPosition !== $dynamicPartBeginPosition) {
                $parts[] = [
                    'type' => 'static',
                    'value' => substr(
                        $template,
                        $currentPosition,
                        $dynamicPartBeginPosition - $currentPosition
                    ),
                ];
            }

            $parts[] = [
                'type' => 'dynamic',
                'value' => $dynamicPartSource,
            ];

            $currentPosition = $dynamicPartBeginPosition + strlen($dynamicPartPlaceholder);
        }

        if ($currentPosition < strlen($template)) {
            $parts[] = [
                'type' => 'static',
                'value' => substr($template, $currentPosition),
            ];
        }

        return $parts;
    }
}
