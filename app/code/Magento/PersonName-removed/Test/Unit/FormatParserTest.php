<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PersonName\Test\Unit;

use Magento\PersonName\FormatInterface;
use Magento\PersonName\FormatParser;
use Magento\PersonName\StaticFormat;
use PHPUnit\Framework\TestCase;

class FormatParserTest extends TestCase
{
    /**
     * @param string $formatTemplate
     * @param array $expectedParsingResult
     * @dataProvider variations
     */
    public function testValidFormats(string $formatTemplate, array $expectedParsingResult)
    {
        $format = new StaticFormat($formatTemplate);
        $parser = new FormatParser();
        $actualParsingResult = $parser->parse($format);
        $this->assertEquals(
            $expectedParsingResult,
            $actualParsingResult,
            'Parsing result is invalid'
        );
    }

    /**
     * @expectedException Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Unknown name format modifier "?".
     */
    public function testUnknownModifier()
    {
        $format = new StaticFormat('{familyName|?}');
        $parser = new FormatParser();
        $parser->parse($format);
    }

    public function variations(): array
    {
        return [
            [
                '{givenName}',
                [
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'givenName',
                        ],
                        'dependsOn' => 'givenName'
                    ]
                ]
            ],
            [
                'anonymous',
                [
                    [
                        'content' => [
                            'type' => 'static',
                            'value' => 'anonymous',
                        ]
                    ]
                ]
            ],
            [
                '{firstName} {lastName}',
                [
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'firstName',
                        ],
                        'dependsOn' => 'firstName'
                    ],
                    [
                        'content' => [
                            'type' => 'static',
                            'value' => ' ',
                        ],
                        'dependsOn' => 'firstName'
                    ],
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'lastName',
                        ],
                        'dependsOn' => 'lastName'
                    ],
                ]
            ],
            [
                '{lastName}, {firstName}',
                [
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'lastName',
                        ],
                        'dependsOn' => 'lastName'
                    ],
                    [
                        'content' => [
                            'type' => 'static',
                            'value' => ', ',
                        ],
                        'dependsOn' => 'lastName'
                    ],
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'firstName',
                        ],
                        'dependsOn' => 'firstName'
                    ],
                ]
            ],
            [
                'delimiterBefore{firstName}delimiterMiddle{lastName}delimiterAfter',
                [
                    [
                        'content' => [
                            'type' => 'static',
                            'value' => 'delimiterBefore',
                        ]
                    ],
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'firstName',
                        ],
                        'dependsOn' => 'firstName'
                    ],
                    [
                        'content' => [
                            'type' => 'static',
                            'value' => 'delimiterMiddle',
                        ],
                        'dependsOn' => 'firstName'
                    ],
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'lastName',
                        ],
                        'dependsOn' => 'lastName'
                    ],
                    [
                        'content' => [
                            'type' => 'static',
                            'value' => 'delimiterAfter',
                        ],
                        'dependsOn' => 'lastName'
                    ],
                ]
            ],
            [
                'delimiterBefore{firstName|b}delimiterMiddle{lastName|r}delimiterAfter',
                [
                    [
                        'content' => [
                            'type' => 'static',
                            'value' => 'delimiterBefore',
                        ],
                        'dependsOn' => 'firstName' // bound to following part name
                    ],
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'firstName',
                        ],
                        'dependsOn' => 'firstName'
                    ],
                    [
                        'content' => [
                            'type' => 'static',
                            'value' => 'delimiterMiddle',
                        ],
                        'dependsOn' => 'firstName'
                    ],
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'lastName',
                        ]
                        // field is required
                    ],
                    [
                        'content' => [
                            'type' => 'static',
                            'value' => 'delimiterAfter',
                        ],
                        // preceding field is required
                    ],
                ]
            ],
            [
                '{firstName|r}{lastName}',
                [
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'firstName',
                        ],
                    ],
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'lastName',
                        ],
                        'dependsOn' => 'lastName',
                    ],
                ],
            ],
            [
            '{firstName}{lastName|rb}',
                [
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'firstName',
                        ],
                        'dependsOn' => 'firstName',
                    ],
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'lastName',
                        ]
                    ],
                ],
            ],
            [
                '{firstName} {}*{nikname|b}* {lastName}',
                [
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'firstName',
                        ],
                        'dependsOn' => 'firstName',
                    ],
                    [
                        'content' => [
                            'type' => 'static',
                            'value' => ' ',
                        ],
                        'dependsOn' => 'firstName'
                    ],
                    [
                        'content' => [
                            'type' => 'static',
                            'value' => '*',
                        ],
                        'dependsOn' => 'nikname'
                    ],
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'nikname',
                        ],
                        'dependsOn' => 'nikname',
                    ],
                    [
                        'content' => [
                            'type' => 'static',
                            'value' => '* ',
                        ],
                        'dependsOn' => 'nikname'
                    ],
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'lastName',
                        ],
                        'dependsOn' => 'lastName',
                    ],
                ],
            ]
        ];
    }
}
