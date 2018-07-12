<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\PersonName;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class LocalizedConfiguredFormatTest extends TestCase
{
    public function testDefaultFormat()
    {
        $renderer = $this->prepareIntegratedRenderer([]);
        $data = $this->prepareIntegratedDataProvider();

        $name = $renderer->render($data);

        $this->assertEquals('John Doe', $name);
    }

    public function testDefaultLongFormat()
    {
        $renderer = $this->prepareIntegratedRenderer(['type' => LocalizedConfiguredFormat::TYPE_LONG]);
        $data = $this->prepareIntegratedDataProvider();

        $name = $renderer->render($data);

        $this->assertEquals('Mr. John P. Doe Jr.', $name);
    }

    /**
     * @magentoConfigFixture default/general/person_name_format/default/global {{var lastname}}, {{var firstname}}
     */
    public function testConfiguredGlobalFormat()
    {
        $renderer = $this->prepareIntegratedRenderer([]);
        $data = $this->prepareIntegratedDataProvider();

        $name = $renderer->render($data);

        $this->assertEquals('Doe, John', $name);
    }

    /**
     * @magentoConfigFixture default/general/person_name_format/default/en_US {{var lastname}}, {{var firstname}}
     * @magentoConfigFixture default/general/locale/code "en_US"
     */
    public function testConfiguredLocalizedFormat()
    {
        $renderer = $this->prepareIntegratedRenderer([]);
        $data = $this->prepareIntegratedDataProvider();

        $name = $renderer->render($data);

        $this->assertEquals('Doe, John', $name);
    }

    /**
     * @magentoConfigFixture default/general/person_name_format/default/global {{var lastname}}, {{var firstname}}
     * @magentoConfigFixture default/general/person_name_format/default/en_US {{var lastname}} {{var firstname}}
     * @magentoConfigFixture default/general/locale/code "en_US"
     */
    public function testConfiguredGlobalAndLocalizedFormat()
    {
        $renderer = $this->prepareIntegratedRenderer([]);
        $data = $this->prepareIntegratedDataProvider();

        $name = $renderer->render($data);

        $this->assertEquals('Doe John', $name);
    }

    private function prepareIntegratedRenderer(array $overriddenDefaults): RendererInterface
    {
        $format = $this->getObjectManager()->create(
            LocalizedConfiguredFormat::class,
            $overriddenDefaults
        );
        $parser = new FormatParser();
        $renderer = new FormattedRenderer($format, $parser);

        return $renderer;
    }

    private function prepareIntegratedDataProvider(): DataProviderInterface
    {
        $personNameData = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'middlename' => 'P.',
            'prefix'=> 'Mr.',
            'suffix' => 'Jr.',
        ];

        $dataProvider = new ArrayProvider($personNameData);

        return $dataProvider;
    }

    private function getObjectManager(): ObjectManagerInterface
    {
        return Bootstrap::getObjectManager();
    }
}
