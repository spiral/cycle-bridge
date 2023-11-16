<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\CycleOrm;

use Spiral\Cycle\Bootloader\SchemaBootloader;
use Cycle\Schema\Generator\SyncTables;
use Cycle\Schema\Registry;
use Spiral\Boot\MemoryInterface;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Cycle\Console\Command\CycleOrm\Generator\ShowChanges;
use Spiral\Cycle\Console\Command\Migrate\AbstractCommand;
use Spiral\Cycle\Schema\Compiler;

final class SyncCommand extends AbstractCommand
{
    protected const NAME = 'cycle:sync';
    protected const DESCRIPTION = 'Sync Cycle ORM schema with database without intermediate migration (risk operation)';

    public function perform(
        SchemaBootloader $bootloader,
        CycleConfig $config,
        Registry $registry,
        MemoryInterface $memory,
    ): int {
        if (!$this->verifyEnvironment(message: 'This operation is not recommended for production environment.')) {
            return self::FAILURE;
        }

        $show = new ShowChanges($this->output);

        $schemaCompiler = Compiler::compile(
            $registry,
            \array_merge($bootloader->getGenerators($config), [$show, new SyncTables()]),
            $config->getSchemaDefaults(),
        );

        $schemaCompiler->toMemory($memory);

        if ($show->hasChanges()) {
            $this->info('ORM Schema has been synchronized with database.');
        }

        return self::SUCCESS;
    }
}
