<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;

/**
 * Contains all Cycle Bridge package bootloaders
 */
final class BridgeBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        // Database
        DatabaseBootloader::class,
        MigrationsBootloader::class,

        // ORM
        SchemaBootloader::class,
        CycleOrmBootloader::class,
        AnnotatedBootloader::class,
        CommandBootloader::class,

        // Validation (Optional)
        ValidationBootloader::class,

        // DataGrid (Optional)
        DataGridBootloader::class,

        // Database Token Storage (Optional)
        AuthTokensBootloader::class,

        // Migrations and Cycle Scaffolders (Optional)
        ScaffolderBootloader::class,

        // Prototyping (Optional)
        PrototypeBootloader::class,
    ];
}
