<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Cycle\Database\DatabaseProviderInterface;
use Cycle\ORM\Config\RelationConfig;
use Cycle\ORM\EntityManager;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Factory;
use Cycle\ORM\FactoryInterface;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;
use Cycle\ORM\Transaction;
use Cycle\ORM\TransactionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Cycle\Injector\RepositoryInjector;

final class CycleOrmBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        DatabaseBootloader::class,
        SchemaBootloader::class,
        AnnotatedBootloader::class,
    ];

    protected const BINDINGS = [
        TransactionInterface::class => Transaction::class,
    ];

    protected const SINGLETONS = [
        ORMInterface::class => ORM::class,
        EntityManagerInterface::class => EntityManager::class,
        FactoryInterface::class => [self::class, 'factory'],
    ];

    public function __construct(
        private ConfiguratorInterface $config
    ) {
    }

    public function boot(
        Container $container,
        FinalizerInterface $finalizer,
        EnvironmentInterface $env,
    ): void {
        $finalizer->addFinalizer(
            static function () use ($container): void {
                if ($container->hasInstance(EntityManagerInterface::class)) {
                    $container->get(EntityManagerInterface::class)->clean();
                }
                if ($container->hasInstance(ORMInterface::class)) {
                    $container->get(ORMInterface::class)->getHeap()->clean();
                }
            }
        );

        $container->bindInjector(RepositoryInterface::class, RepositoryInjector::class);

        $this->initOrmConfig($env);
    }

    public function start(AbstractKernel $kernel): void
    {
        $kernel->started(static function (ContainerInterface $container, CycleConfig $config): void {
            if ($config->warmup()) {
                $orm = $container->get(ORMInterface::class);
                if (\method_exists($orm, 'prepareServices')) {
                    $orm->prepareServices();
                }
            }
        });
    }

    private function factory(
        DatabaseProviderInterface $dbal,
        Container $container,
        CycleConfig $config
    ): FactoryInterface {
        $relationConfig = new RelationConfig(
            RelationConfig::getDefault()->toArray() + $config->getCustomRelations()
        );

        $factory = new Factory(
            $dbal,
            $relationConfig,
            $container,
            $config->getDefaultCollectionFactory()
        );

        foreach ($config->getCollectionFactories() as $alias => $collectionFactory) {
            $factory = $factory->withCollectionFactory($alias, $collectionFactory);
        }

        return $factory;
    }

    private function initOrmConfig(EnvironmentInterface $env): void
    {
        $this->config->setDefaults(
            CycleConfig::CONFIG,
            [
                'schema' => [
                    'cache' => $env->get('CYCLE_SCHEMA_CACHE', false),
                    'generators' => null,
                    'defaults' => [],
                    'collections' => [],
                ],
                'warmup' => $env->get('CYCLE_SCHEMA_WARMUP', false),
            ]
        );
    }
}
