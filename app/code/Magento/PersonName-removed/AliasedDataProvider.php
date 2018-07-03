<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PersonName;

class AliasedDataProvider implements DataProviderInterface
{
    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    /**
     * @var array
     */
    private $aliases;

    /**
     * @param DataProviderInterface $dataProvider
     * @param array $aliases
     * @throws \InvalidArgumentException if contains circular aliases
     */
    public function __construct(DataProviderInterface $dataProvider, array $aliases)
    {
        $this->dataProvider = $dataProvider;
        $this->aliases = $aliases;
        $this->assertNoCircularAliases();
    }

    /**
     * @inheritDoc
     */
    public function supports(string $key): bool
    {
        return $this->dataProvider->supports($this->resolveAlias($key));
    }

    /**
     * @inheritDoc
     */
    public function contains(string $key): bool
    {
        return $this->dataProvider->contains($this->resolveAlias($key));
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): string
    {
        return $this->dataProvider->get($this->resolveAlias($key));
    }

    /**
     * Resolve alias for requested key
     *
     * @param string $key
     * @return string
     * @throws \InvalidArgumentException if alias can not be resolved
     */
    private function resolveAlias(string $key): string
    {
        $tracking = [];
        while (isset($this->aliases[$key])) {
            $tracking[] = $key;
            $key = $this->aliases[$key];
            if (in_array($key, $tracking)) {
                throw new \InvalidArgumentException('Circular aliasing detected.');
            }
        }
        return $key;
    }

    /**
     * @throws \InvalidArgumentException if contains circular aliases
     */
    private function assertNoCircularAliases()
    {
        $circular = [];
        foreach ($this->aliases as $alias) {
            try {
                $this->resolveAlias($alias);
            } catch (\InvalidArgumentException $e) {
                $circular[] = $alias;
            }
        }

        if (!empty($circular)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Aliases configuration is invalid. Please resolve circular dependencies for: %s',
                    join(', ', $circular)
                )
            );
        }
    }
}