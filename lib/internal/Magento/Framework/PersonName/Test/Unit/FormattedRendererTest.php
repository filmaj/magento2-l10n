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

        $dataProvider = new AliasedDataProvider(
            new ArrayProvider($this->createPersonDataObject()),
            [
                'firstName' => 'firstname',
                'givenName' => 'firstname',
                'lastName' => 'lastname',
                'familyName' => 'lastname',
                'middleName' => 'middlename',
            ]
        );

        $actualResult = $renderer->render($dataProvider);
        $this->assertEquals($expectedResult, $actualResult);
    }


    private function createPersonDataObject()
    {
        return new DataObject([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'middlename' => 'P.',
            'prefix'=> 'Mr.',
            'suffix' => 'Jr.',
        ]);
    }

    public function variations()
    {
        return [
            [
                '{firstName} {lastName}',
                'John Doe'
            ],
            [
                '{givenName} {familyName}',
                'John Doe'
            ],
            [
                '{lastName}, {firstName}',
                'Doe, John'
            ],
            [
                '{prefix} {givenName} {middleName} {lastName} {suffix}',
                'Mr. John P. Doe Jr.'
            ],
            [
                '{givenName} {}*{nikname|b}* {familyName}',
                'John Doe'
            ]
        ];
    }
}