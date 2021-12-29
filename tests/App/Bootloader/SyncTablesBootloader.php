<?php

declare(strict_types=1);

namespace Spiral\App\Bootloader;

use Cycle\Schema\Generator\SyncTables;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Cycle\Bootloader\SchemaBootloader;

final class SyncTablesBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        SchemaBootloader::class,
    ];

    public function boot(SchemaBootloader $schema): void
    {
        $schema->addGenerator(SchemaBootloader::GROUP_POSTPROCESS, SyncTables::class);
    }
}
