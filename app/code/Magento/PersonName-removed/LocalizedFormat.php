<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PersonName;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface;

class LocalizedFormat implements FormatInterface
{
    const DEFAULT_FORMAT = '{firstName} {lastName}';

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
    private $default;

    /**
     * @param ResolverInterface $localeResolver
     * @param ScopeConfigInterface $config
     * @param string $configScope
     * @param string $default
     */
    public function __construct(
        ResolverInterface $localeResolver,
        ScopeConfigInterface $config,
        string $configScope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        string $default = self::DEFAULT_FORMAT
    ) {
        $this->localeResolver = $localeResolver;
        $this->config = $config;
        $this->configScope = $configScope;
        $this->default = $default;
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

        $genericFormatConfigPath = $this->getGlobalFormatConfigPath();
        $value = $this->readConfigValue($genericFormatConfigPath);
        if (!empty($value)) {
            return $value;
        }

        return self::DEFAULT_FORMAT;
    }

    /**
     * Fetch configuration path for a current locale
     *
     * @return string
     */
    private function getLocalizedFormatConfigPath(): string
    {
        $locale = $this->localeResolver->getLocale();
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
        $path = sprintf('general/person_name_format/%s', $section);
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
