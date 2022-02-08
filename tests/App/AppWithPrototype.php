<?php

declare(strict_types=1);

namespace Spiral\App;

use Spiral\Bootloader as Framework;
use Spiral\Cycle\Bootloader as CycleBridge;

// PrototypeBootloader accesses the Cycle configuration and
// breaks all tests that use the method Spiral\Tests\BaseTest->updateConfig()
class AppWithPrototype extends App
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

        // Prototyping
        CycleBridge\PrototypeBootloader::class,
    ];
}
