<?php

declare(strict_types=1);

namespace Spiral\App;

use Spiral\Bootloader as Framework;
use Spiral\Core\Container;
use Spiral\Cycle\Bootloader as CycleBridge;
use Spiral\Framework\Kernel;

class App extends Kernel
{
    protected const LOAD = [
        // Databases
        CycleBridge\DatabaseBootloader::class,
        CycleBridge\MigrationsBootloader::class,

        // ORM
        CycleBridge\SchemaBootloader::class,
        CycleBridge\CycleOrmBootloader::class,
        CycleBridge\AnnotatedBootloader::class,
        CycleBridge\CommandBootloader::class,

        Bootloader\SyncTablesBootloader::class,

        // DataGrid
        CycleBridge\DataGridBootloader::class,

        // Auth
        CycleBridge\AuthTokensBootloader::class,

        // Framework commands
        Framework\CommandBootloader::class,
    ];

    /**
     * Get object from the container.
     */
    public function get(string $alias, string $context = null)
    {
        return $this->container->get($alias, $context);
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}
