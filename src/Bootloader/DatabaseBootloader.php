<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseManager;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\Database\LoggerFactoryInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Cycle\Injector\DatabaseInjector;
use Spiral\Cycle\LoggerFactory;

final class DatabaseBootloader extends Bootloader implements SingletonInterface
{
    protected const SINGLETONS = [
        DatabaseManager::class => [self::class, 'initManager'],
        DatabaseProviderInterface::class => DatabaseManager::class,
        LoggerFactoryInterface::class => LoggerFactory::class,
    ];

    /**
     * Init database config.
     */
    public function init(Container $container, ConfiguratorInterface $config): void
    {
        $config->setDefaults(
            DatabaseConfig::CONFIG,
            [
                'logger' => [
                    'default' => null,
                    'drivers' => [],
                ],
                'default' => 'default',
                'aliases' => [],
                'databases' => [],
                'drivers' => [],
            ]
        );

        $container->bindInjector(DatabaseInterface::class, DatabaseInjector::class);
    }

    protected function initManager(
        DatabaseConfig $config,
        LoggerFactoryInterface $loggerFactory
    ): DatabaseProviderInterface {
        return new DatabaseManager($config, $loggerFactory);
    }
}
