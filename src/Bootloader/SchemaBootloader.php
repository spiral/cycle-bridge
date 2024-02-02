<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Cycle\Schema\Defaults;
use Cycle\Schema\Generator;
use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Provider\SchemaProviderInterface;
use Cycle\Schema\Provider\Support\SchemaProviderPipeline;
use Cycle\Schema\Registry;
use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container;
use Spiral\Core\FactoryInterface;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;

final class SchemaBootloader extends Bootloader implements Container\SingletonInterface
{
    public const GROUP_INDEX = 'index';
    public const GROUP_RENDER = 'render';
    public const GROUP_POSTPROCESS = 'postprocess';

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

    public function defineDependencies(): array
    {
        return [
            TokenizerBootloader::class,
            CycleOrmBootloader::class,
        ];
    }

    public function defineBindings(): array
    {
        return [
            Registry::class => [self::class, 'initRegistry'],
            SchemaInterface::class => static fn (SchemaProviderInterface $provider): SchemaInterface => new Schema(
                $provider->read() ?? []
            ),
            SchemaProviderInterface::class => static function (ContainerInterface $container): SchemaProviderInterface {
                /** @var CycleConfig $config */
                $config = $container->get(CycleConfig::class);

                return (new SchemaProviderPipeline($container))->withConfig($config->getSchemaProviders());
            },
        ];
    }

    public function addGenerator(string $group, string|GeneratorInterface $generator): void
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

    private function initRegistry(FactoryInterface $factory, CycleConfig $config): Registry
    {
        $defaults = new Defaults();
        $defaults->merge($config->getSchemaDefaults());

        return $factory->make(Registry::class, ['defaults' => $defaults]);
    }
}

