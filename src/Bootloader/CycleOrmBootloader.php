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
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Transaction;
use Cycle\ORM\TransactionInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Cycle\RepositoryInjector;

final class CycleOrmBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        DatabaseBootloader::class,
        SchemaBootloader::class,
        AnnotatedBootloader::class,
    ];

    protected const BINDINGS = [
        TransactionInterface::class => Transaction::class,
        EntityManagerInterface::class => EntityManager::class,
    ];

    protected const SINGLETONS = [
        ORMInterface::class => ORM::class,
        ORM::class => [self::class, 'orm'],
        FactoryInterface::class => [self::class, 'factory'],
    ];

    public function __construct(
        private ConfiguratorInterface $config
    ) {
    }

    public function boot(
        Container $container,
        FinalizerInterface $finalizer,
        EnvironmentInterface $env
    ): void
    {
        $finalizer->addFinalizer(
            function () use ($container): void {
                if ($container->hasInstance(ORMInterface::class)) {
                    $container->get(ORMInterface::class)->getHeap()->clean();
                }
            }
        );

        $container->bindInjector(RepositoryInterface::class, RepositoryInjector::class);

        $this->initOrmConfig($env);
    }

    private function orm(
        FactoryInterface $factory,
        SchemaInterface $schema = null
    ): ORMInterface {
        return new ORM($factory, $schema);
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

    private function initOrmConfig(EnvironmentInterface $env)
    {
        $this->config->setDefaults(
            CycleConfig::CONFIG,
            [
                'schema' => [
                    'cache' => true,
                    'generators' => null,
                    'defaults' => [],
                    'collections' => [],
                ],
            ]
        );
    }
}
