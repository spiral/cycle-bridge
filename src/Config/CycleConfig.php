<?php

declare(strict_types=1);

namespace Spiral\Cycle\Config;

use Cycle\ORM\Collection\ArrayCollectionFactory;
use Cycle\ORM\Collection\CollectionFactoryInterface;
use Spiral\Core\InjectableConfig;

final class CycleConfig extends InjectableConfig
{
    public const CONFIG = 'cycle';

    public function getDefaultCollectionFactory(): CollectionFactoryInterface
    {
        $config = $this->config['schema']['collections'] ?? [];
        $defaultSchema = $config['default'] ?? 'array';

        if (
            isset($config['factories'][$defaultSchema])
            && $config['factories'][$defaultSchema] instanceof CollectionFactoryInterface
        ) {
            return $config['factories'][$defaultSchema];
        }

        return new ArrayCollectionFactory();
    }

    /**
     * @return array<string, CollectionFactoryInterface>
     */
    public function getCollectionFactories(): array
    {
        return (array)($this->config['schema']['collections']['factories'] ?? []);
    }

    public function getSchemaGenerators(): ?array
    {
        return $this->config['schema']['generators'] ?? null;
    }

    public function getSchemaDefaults(): array
    {
        return (array)($this->config['schema']['defaults'] ?? []);
    }

    public function cacheSchema(): bool
    {
        return (bool)($this->config['schema']['cache'] ?? false);
    }

    public function getCustomRelations(): array
    {
        return (array)($this->config['customRelations'] ?? []);
    }

    public function warmup(): bool
    {
        return (bool)($this->config['warmup'] ?? false);
    }
}
