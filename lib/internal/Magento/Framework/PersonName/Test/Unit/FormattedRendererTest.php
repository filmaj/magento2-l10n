<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\PersonName\Test\Unit;

use Magento\Framework\DataObject;
use Magento\Framework\PersonName\AliasedDataProvider;
use Magento\Framework\PersonName\ArrayProvider;
use Magento\Framework\PersonName\FormatParser;
use Magento\Framework\PersonName\FormatProviderInterface;
use Magento\Framework\PersonName\FormattedRenderer;
use Magento\Framework\PersonName\StaticFormat;
use PHPUnit\Framework\TestCase;

class FormattedRendererTest extends TestCase
{
    /**
     * @dataProvider variations
     */
    public function testRender(string $template, string $expectedResult)
    {
        $format = new StaticFormat($template);
        $parser = new FormatParser();
        $renderer = new FormattedRenderer($format, $parser);

        $dataProvider = new ArrayProvider($this->createPersonDataObject());

        $actualResult = $renderer->render($dataProvider);
        $this->assertEquals($expectedResult, $actualResult);
    }


    private function createPersonDataObject()
    {
        return [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'middlename' => 'P.',
            'prefix'=> 'Mr.',
            'suffix' => 'Jr.',
        ];
    }

    public function variations()
    {
        return [
            [
                '{{var firstname}}{{depend lastname}} {{var lastname}}{{/depend}}',
                'John Doe'
            ],
            [
                '{{var lastname}}{{depend firstname}}, {{var firstname}}{{/depend}}',
                'Doe, John'
            ],
            [
                '{{depend prefix}}{{var prefix}} {{/depend}}{{var firstname}} ' .
                '{{depend middlename}}{{var middlename}} {{/depend}}{{var lastname}}' .
                '{{depend suffix}} {{var suffix}}{{/depend}}',
                'Mr. John P. Doe Jr.'
            ],
        ];
    }
}