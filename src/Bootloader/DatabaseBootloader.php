<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\Database;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseManager;
use Cycle\Database\DatabaseProviderInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Cycle\LoggerFactory;

final class DatabaseBootloader extends Bootloader implements SingletonInterface
{
    protected const SINGLETONS = [
        DatabaseManager::class => [self::class, 'initManager'],
        DatabaseProviderInterface::class => DatabaseManager::class
    ];

    protected const BINDINGS = [
        DatabaseInterface::class => [self::class, 'getDefaultDatabase'],
    ];

    /**
     * Init database config.
     */
    public function boot(ConfiguratorInterface $config): void
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
    }

    protected function initManager(DatabaseConfig $config, LoggerFactory $loggerFactory): DatabaseProviderInterface
    {
        return new DatabaseManager($config, $loggerFactory);
    }

    protected function getDefaultDatabase(DatabaseProviderInterface $manager): DatabaseInterface
    {
        return $manager->database();
    }
}

