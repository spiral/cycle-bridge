<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Cycle\ORM\SchemaInterface;
use Cycle\Schema\Defaults;
use Cycle\Schema\Generator;
use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Registry;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\MemoryInterface;
use Spiral\Core\Container;
use Spiral\Core\FactoryInterface;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Cycle\Schema\Compiler;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;

final class SchemaBootloader extends Bootloader implements Container\SingletonInterface
{
    public const GROUP_INDEX = 'index';
    public const GROUP_RENDER = 'render';
    public const GROUP_POSTPROCESS = 'postprocess';

    protected const DEPENDENCIES = [
        TokenizerBootloader::class,
        CycleOrmBootloader::class,
    ];

    protected const BINDINGS = [
        SchemaInterface::class => [self::class, 'schema'],
        Registry::class => [self::class, 'initRegistry']
    ];

    /** @var string[][]|GeneratorInterface[][] */
    private array $defaultGenerators;

    public function __construct(
        private readonly Container $container
    ) {
        $this->defaultGenerators = [
            self::GROUP_INDEX => [
                // find available entities
            ],
            self::GROUP_RENDER => [
                // render tables and relations
                Generator\ResetTables::class,
                Generator\GenerateRelations::class,
                Generator\GenerateModifiers::class,
                Generator\ValidateEntities::class,
                Generator\RenderTables::class,
                Generator\RenderRelations::class,
                Generator\RenderModifiers::class,
                Generator\ForeignKeys::class,
            ],
            self::GROUP_POSTPROCESS => [
                // post processing
                Generator\GenerateTypecast::class,
            ],
        ];
    }

    public function addGenerator(string $group, string $generator): void
    {
        $this->defaultGenerators[$group][] = $generator;
    }

    /**
     * @return GeneratorInterface[]
     * @throws \Throwable
     */
    public function getGenerators(CycleConfig $config): array
    {
        $generators = $config->getSchemaGenerators();
        if (\is_array($generators)) {
            $generators = [self::GROUP_INDEX => $generators];
        } else {
            $generators = $this->defaultGenerators;
        }

        $result = [];
        foreach ($generators as $group) {
            foreach ($group as $generator) {
                if (\is_object($generator) && ! $generator instanceof Container\Autowire) {
                    $result[] = $generator;
                } else {
                    $result[] = $this->container->get($generator);
                }
            }
        }

        return $result;
    }

    /**
     * @throws \Throwable
     */
    protected function schema(MemoryInterface $memory, CycleConfig $config): SchemaInterface
    {
        $schemaCompiler = Compiler::fromMemory($memory);

        if ($schemaCompiler->isEmpty() || ! $config->cacheSchema()) {
            $schemaCompiler = Compiler::compile(
                $this->container->get(Registry::class),
                $this->getGenerators($config),
                $config->getSchemaDefaults()
            );

            $schemaCompiler->toMemory($memory);
        }

        return $schemaCompiler->toSchema();
    }

    private function initRegistry(FactoryInterface $factory, CycleConfig $config): Registry
    {
        $defaults = new Defaults();
        $defaults->merge($config->getSchemaDefaults());

        return $factory->make(Registry::class, ['defaults' => $defaults]);
    }
}

