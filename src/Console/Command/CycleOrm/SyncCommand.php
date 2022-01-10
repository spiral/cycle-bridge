<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\CycleOrm;

use Spiral\Cycle\Bootloader\SchemaBootloader;
use Cycle\Schema\Generator\SyncTables;
use Cycle\Schema\Registry;
use Spiral\Boot\MemoryInterface;
use Spiral\Console\Command;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Cycle\Console\Command\CycleOrm\Generator\ShowChanges;
use Spiral\Cycle\SchemaCompiler;

final class SyncCommand extends Command
{
    protected const NAME = 'cycle:sync';
    protected const DESCRIPTION = 'Sync Cycle ORM schema with database without intermediate migration (risk operation)';

    public function perform(
        SchemaBootloader $bootloader,
        CycleConfig $config,
        Registry $registry,
        MemoryInterface $memory
    ): void {
        $show = new ShowChanges($this->output);

        $schemaCompiler = SchemaCompiler::compile(
            $registry,
            array_merge($bootloader->getGenerators($config), [$show, new SyncTables()])
        );
        $schemaCompiler->toMemory($memory);

        if ($show->hasChanges()) {
            $this->writeln("\n<info>ORM Schema has been synchronized</info>");
        }
    }
}
