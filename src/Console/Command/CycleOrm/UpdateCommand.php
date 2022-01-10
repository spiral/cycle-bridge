<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\CycleOrm;

use Cycle\Schema\Registry;
use Spiral\Boot\MemoryInterface;
use Spiral\Console\Command;
use Spiral\Console\Console;
use Spiral\Cycle\Bootloader\SchemaBootloader;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Cycle\SchemaCompiler;

final class UpdateCommand extends Command
{
    protected const NAME = 'cycle';
    protected const DESCRIPTION = 'Update (init) cycle schema from database and annotated classes';

    public function perform(
        SchemaBootloader $bootloader,
        CycleConfig $config,
        Registry $registry,
        MemoryInterface $memory,
        Console $console
    ): int {
        $this->write('Updating ORM schema... ');

        SchemaCompiler::compile(
            $registry,
            $bootloader->getGenerators($config)
        )->toMemory($memory);

        $this->writeln('<info>done</info>');

        return self::SUCCESS;
    }
}
