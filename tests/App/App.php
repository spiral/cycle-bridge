<?php

declare(strict_types=1);

namespace Spiral\App;

use Spiral\Boot\BootloadManager;
use Spiral\Bootloader as Framework;
use Spiral\Console\Console;
use Spiral\Core\Container;
use Spiral\Cycle\Bootloader as CycleBridge;
use Spiral\Framework\Kernel;

class App extends Kernel
{
    protected const LOAD = [
        // Framework commands
        Framework\ConsoleBootloader::class,
        Framework\CommandBootloader::class,

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
        // Validation
        CycleBridge\ValidationBootloader::class,
    ];

    /**
     * Get object from the container.
     *
     * @template T
     *
     * @param class-string<T>|string $alias
     *
     * @return T
     * @psalm-return ($alias is class-string ? T : mixed)
     *
     * @throws \Throwable
     */
    public function get(string $alias, string $context = null): mixed
    {
        return $this->container->get($alias, $context);
    }

    public function getBootloadManager(): BootloadManager
    {
        return $this->bootloader;
    }

    public function console(): Console
    {
        return $this->get(Console::class);
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}
