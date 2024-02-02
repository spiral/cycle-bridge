<?php

declare(strict_types=1);

namespace Spiral\Cycle\Schema\Provider;

use Cycle\Annotated\Embeddings;
use Cycle\Annotated\Entities;
use Cycle\Annotated\Locator\EmbeddingLocatorInterface;
use Cycle\Annotated\Locator\EntityLocatorInterface;
use Cycle\Annotated\MergeColumns;
use Cycle\Annotated\MergeIndexes;
use Cycle\Annotated\TableInheritance;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\Schema\Compiler;
use Cycle\Schema\Defaults;
use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Provider\Exception\ConfigurationException;
use Cycle\Schema\Provider\SchemaProviderInterface;
use Cycle\Schema\Registry;
use Psr\Container\ContainerInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Cycle\Config\CycleConfig;

final class AnnotatedSchemaProvider implements SchemaProviderInterface
{
    private int $tableNaming = Entities::TABLE_NAMING_PLURAL;

    /**
     * @var array<GeneratorInterface>
     */
    private array $generators = [];

    public function __construct(
        array $generators,
        private readonly ReaderInterface $reader,
        private readonly EntityLocatorInterface $entityLocator,
        private readonly EmbeddingLocatorInterface $embeddingLocator,
        private readonly ContainerInterface $container,
    ) {
        $this->generators = \array_merge($this->getAnnotatedGenerators(), $generators);
    }

    /**
     * Create a configuration array for the {@see self::withConfig()} method.
     */
    public static function config(int $tableNaming): array
    {
        return [
            'tableNaming' => $tableNaming,
        ];
    }

    public function withConfig(array $config): self
    {
        if (\array_key_exists('tableNaming', $config) && !\is_int($config['tableNaming'])) {
            throw new ConfigurationException('The `tableNaming` parameter must be an integer.');
        }

        $new = clone $this;
        $new->tableNaming = $config['tableNaming'] ?? $this->tableNaming;

        return $new;
    }

    /**
     * @param array<GeneratorInterface> $generators
     */
    public function withGenerators(array $generators): self
    {
        $new = clone $this;
        $new->generators = \array_merge($this->getAnnotatedGenerators(), $generators);

        return $new;
    }

    public function read(?SchemaProviderInterface $nextProvider = null): ?array
    {
        $defaults = new Defaults();
        $defaults->merge($this->container->get(CycleConfig::class)->getSchemaDefaults());

        $schema = (new Compiler())->compile(
            new Registry($this->container->get(DatabaseProviderInterface::class), $defaults),
            $this->generators
        );

        return \count($schema) !== 0 || $nextProvider === null ? $schema : $nextProvider->read();
    }

    public function getGenerators(): array
    {
        return $this->generators;
    }

    public function clear(): bool
    {
        return false;
    }

    private function getAnnotatedGenerators(): array
    {
        return [
            new Embeddings($this->embeddingLocator, $this->reader),
            new Entities($this->entityLocator, $this->reader, $this->tableNaming),
            new TableInheritance($this->reader),
            new MergeColumns($this->reader),
            new MergeIndexes($this->reader),
        ];
    }
}
