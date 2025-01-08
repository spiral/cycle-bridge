<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\CycleOrm;

use Cycle\Schema\Compiler;
use Cycle\Schema\Generator\Migrations\Strategy\GeneratorStrategyInterface;
use Cycle\Schema\Generator\Migrations\Strategy\MultipleFilesStrategy;
use Cycle\Schema\Generator\PrintChanges;
use Spiral\Core\BinderInterface;
use Spiral\Cycle\Bootloader\SchemaBootloader;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Cycle\Console\Command\Migrate\AbstractCommand;
use Cycle\Migrations\State;
use Cycle\Schema\Generator\Migrations\GenerateMigrations;
use Cycle\Schema\Registry;
use Spiral\Console\Console;
use Cycle\Migrations\Migrator;
use Spiral\Cycle\Schema\Provider\AnnotatedSchemaProvider;
use Symfony\Component\Console\Input\InputOption;

final class MigrateCommand extends AbstractCommand
{
    protected const NAME = 'cycle:migrate';
    protected const DESCRIPTION = 'Generate ORM schema migrations';
    protected const OPTIONS = [
        ['split', 'p', InputOption::VALUE_NONE, 'Split generated migration into multiple files.'],
        ['run', 'r', InputOption::VALUE_NONE, 'Automatically run generated migration.'],
    ];

    public function perform(
        SchemaBootloader $bootloader,
        CycleConfig $config,
        Registry $registry,
        Migrator $migrator,
        Console $console,
        AnnotatedSchemaProvider $provider,
    ): int {
        $migrator->configure();

        foreach ($migrator->getMigrations() as $migration) {
            if ($migration->getState()->getStatus() !== State::STATUS_EXECUTED) {
                $this->error('Outstanding migrations found.');

                if ($this->isInteractive() && $this->output->confirm('Do you want to run `migrate` now?')) {
                    $console->run('migrate', [], $this->output);
                } else {
                    $this->error('Please run `migrate` first.');
                    return self::SUCCESS;
                }
            }
        }

        $this->comment('Detecting schema changes...');

        $provider = $provider->withGenerators(\array_merge($bootloader->getGenerators($config), [
            $print = new PrintChanges($this->output)
        ]));
        $provider->read();

        if ($print->hasChanges()) {
            if ($this->option('split')) {
                \assert($this->container instanceof BinderInterface);
                $this->container->bind(GeneratorStrategyInterface::class, MultipleFilesStrategy::class);
            }

            $migrations = $this->container->get(GenerateMigrations::class);

            (new Compiler())->compile($registry, \array_merge($provider->getGenerators(), [$migrations]));

            if ($this->option('run')) {
                $console->run('migrate', [], $this->output);
            }
        }

        return self::SUCCESS;
    }
}
