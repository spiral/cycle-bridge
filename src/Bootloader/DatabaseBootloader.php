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
        DatabaseProviderInterface::class => [self::class, 'initManager'],
    ];

    protected const BINDINGS = [
        DatabaseInterface::class => Database::class,
    ];

    public function __construct(
        private ConfiguratorInterface $config
    ) {
    }

    /**
     * Init database config.
     */
    public function boot(): void
    {
        $this->config->setDefaults(
            'database',
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

    protected function initManager(
        DatabaseConfig $config,
        LoggerFactory $loggerFactory
    ): DatabaseProviderInterface {
        return new DatabaseManager($config, $loggerFactory);
    }
}

