<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\PersonName\Test\Unit;

use Magento\Framework\PersonName\FormatParser;
use Magento\Framework\PersonName\StaticFormat;
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

    public function variations(): array
    {
        return [
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
                '{{var givenName}}',
                [
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'givenName',
                        ]
                    ]
                ],
            ],
            [
                '{{depend givenName}}{{var givenName}}{{/depend}}',
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
                '{{depend firstName}}{{var firstName}} {{/depend}}{{var lastName}}',
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
                        ]
                    ],
                ]
            ],
            [
                '{{var lastName}}{{depend firstName}}, {{var firstName}}{{/depend}}',
                [
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'lastName',
                        ]
                    ],
                    [
                        'content' => [
                            'type' => 'static',
                            'value' => ', ',
                        ],
                        'dependsOn' => 'firstName'
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
                '{{depend prefix}}{{var prefix}} {{/depend}}{{var firstname}} ' .
                '{{depend middlename}}{{var middlename}} {{/depend}}{{var lastname}}' .
                '{{depend suffix}} {{var suffix}}{{/depend}}',
                [
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'prefix',
                        ],
                        'dependsOn' => 'prefix'
                    ],
                    [
                        'content' => [
                            'type' => 'static',
                            'value' => ' ',
                        ],
                        'dependsOn' => 'prefix'
                    ],
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'firstname',
                        ],
                    ],
                    [
                        'content' => [
                            'type' => 'static',
                            'value' => ' ',
                        ],
                    ],
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'middlename',
                        ],
                        'dependsOn' => 'middlename'
                    ],
                    [
                        'content' => [
                            'type' => 'static',
                            'value' => ' ',
                        ],
                        'dependsOn' => 'middlename'
                    ],
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'lastname',
                        ],
                    ],
                    [
                        'content' => [
                            'type' => 'static',
                            'value' => ' ',
                        ],
                        'dependsOn' => 'suffix'
                    ],
                    [
                        'content' => [
                            'type' => 'dynamic',
                            'value' => 'suffix',
                        ],
                        'dependsOn' => 'suffix'
                    ],
                ]
            ]
        ];
    }
}
