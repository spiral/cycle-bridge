<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\CycleOrm;

use Spiral\Cycle\Bootloader\SchemaBootloader;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Cycle\Console\Command\CycleOrm\Generator\ShowChanges;
use Spiral\Cycle\Console\Command\Migrate\AbstractCommand;
use Cycle\Migrations\State;
use Cycle\Schema\Generator\Migrations\GenerateMigrations;
use Spiral\Cycle\Schema\Compiler;
use Cycle\Schema\Registry;
use Spiral\Boot\MemoryInterface;
use Spiral\Console\Console;
use Cycle\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;

final class MigrateCommand extends AbstractCommand
{
    protected const NAME = 'cycle:migrate';
    protected const DESCRIPTION = 'Generate ORM schema migrations';
    protected const OPTIONS = [
        ['run', 'r', InputOption::VALUE_NONE, 'Automatically run generated migration.'],
    ];

    public function perform(
        SchemaBootloader $bootloader,
        CycleConfig $config,
        Registry $registry,
        MemoryInterface $memory,
        GenerateMigrations $migrations,
        Migrator $migrator,
        Console $console
    ): int {
        $migrator->configure();

        foreach ($migrator->getMigrations() as $migration) {
            if ($migration->getState()->getStatus() !== State::STATUS_EXECUTED) {
                $this->writeln('<fg=red>Outstanding migrations found, run `migrate` first.</fg=red>');
                return self::FAILURE;
            }
        }

        $schemaCompiler = Compiler::compile(
            $registry,
            array_merge($bootloader->getGenerators($config), [
                $show = new ShowChanges($this->output)
            ])
        );

        $schemaCompiler->toMemory($memory);

        if ($show->hasChanges()) {
            (new \Cycle\Schema\Compiler())->compile($registry, [$migrations]);

            if ($this->option('run')) {
                $console->run('migrate', [], $this->output);
            }
        }

        return self::SUCCESS;
    }
}
