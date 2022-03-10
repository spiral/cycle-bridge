<?php

declare(strict_types=1);

namespace Spiral\App\Bootloader;

use Cycle\Schema\Generator\SyncTables;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Cycle\Bootloader\SchemaBootloader;

final class SyncTablesBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        SchemaBootloader::class,
    ];

    public function boot(SchemaBootloader $schema, EnvironmentInterface $env): void
    {
        if (! $env->get('USE_MIGRATIONS')) {
            $schema->addGenerator(SchemaBootloader::GROUP_POSTPROCESS, SyncTables::class);
        }
    }
}
