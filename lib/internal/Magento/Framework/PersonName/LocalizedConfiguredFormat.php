<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\PersonName;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface;

class LocalizedConfiguredFormat implements FormatInterface
{
    const TYPE_DEFAULT = 'default';
    const TYPE_LONG = 'long';

    private const DEFAULTS = [
        self::TYPE_DEFAULT => '{{var firstname}}{{depend lastname}} {{var lastname}}{{/depend}}',
        self::TYPE_LONG => '{{depend prefix}}{{var prefix}} {{/depend}}' .
            '{{var firstname}} ' .
            '{{depend middlename}}{{var middlename}} {{/depend}}' .
            '{{var lastname}}' .
            '{{depend suffix}} {{var suffix}}{{/depend}}',
    ];

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var string
     */
    private $configScope;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $default;

    /**
     * LocalizedConfiguredFormat constructor.
     * @param ResolverInterface $localeResolver
     * @param ScopeConfigInterface $config
     * @param string $configScope
     * @param string $type
     * @param string $default
     */
    public function __construct(
        ResolverInterface $localeResolver,
        ScopeConfigInterface $config,
        string $configScope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        string $type = self::TYPE_DEFAULT,
        string $default = null
    ) {
        $this->localeResolver = $localeResolver;
        $this->config = $config;
        $this->configScope = $configScope;
        $this->type = $type;
        if (null !== $default) {
            $this->default = $default;
        } else if (isset(self::DEFAULTS[$type])) {
            $this->default = self::DEFAULTS[$type];
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                'Default format of type "%s" is unknown and not provided in constructor.',
                    $type
                )
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getTemplate(): string
    {
        $localizedFormatConfigPath = $this->getLocalizedFormatConfigPath();
        $value = $this->readConfigValue($localizedFormatConfigPath);
        if (!empty($value)) {
            return $value;
        }

        $globalFormatConfigPath = $this->getGlobalFormatConfigPath();
        $value = $this->readConfigValue($globalFormatConfigPath);
        if (!empty($value)) {
            return $value;
        }

        return $this->default;
    }

    /**
     * Fetch configuration path for a current locale
     *
     * @return string
     */
    private function getLocalizedFormatConfigPath(): string
    {
        $locale = $this->localeResolver->getLocale();
        if (null === $locale) {
            $locale = $this->localeResolver->getDefaultLocale();
        }
        $path = $this->getFormatConfigPath($locale);
        return $path;
    }

    /**
     * Fetch configuration path that may be applied for all locales
     *
     * @return string
     */
    private function getGlobalFormatConfigPath(): string
    {
        $path = $this->getFormatConfigPath('global');
        return $path;
    }

    /**
     * Fetch configuration path
     *
     * @param string $section
     * @return string
     */
    private function getFormatConfigPath(string $section): string
    {
        $path = sprintf('general/person_name_format/%s/%s', $this->type, $section);
        return $path;
    }

    /**
     * @param string $value
     * @return string|null
     */
    private function readConfigValue(string $value)
    {
        $value = $this->config->getValue($value, $this->configScope);
        return $value;
    }
}
