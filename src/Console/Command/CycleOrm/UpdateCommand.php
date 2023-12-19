<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\CycleOrm;

use Cycle\Schema\Registry;
use Spiral\Boot\MemoryInterface;
use Spiral\Console\Command;
use Spiral\Cycle\Bootloader\SchemaBootloader;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Cycle\Schema\Compiler;

final class UpdateCommand extends Command
{
    protected const NAME = 'cycle';
    protected const DESCRIPTION = 'Update (init) cycle schema from database and annotated classes';

    public function perform(
        SchemaBootloader $bootloader,
        CycleConfig $config,
        Registry $registry,
        MemoryInterface $memory,
    ): int {
        $this->info('Updating ORM schema... ');

        Compiler::compile(
            $registry,
            $bootloader->getGenerators($config),
            $config->getSchemaDefaults(),
        )->toMemory($memory);

        $this->info('Schema has been updated.');

        return self::SUCCESS;
    }
}
