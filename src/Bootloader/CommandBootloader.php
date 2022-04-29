<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Cycle\Database\DatabaseProviderInterface;
use Cycle\Migrations\Migrator;
use Cycle\ORM\ORMInterface;
use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Cycle\Console\Command\CycleOrm;
use Spiral\Cycle\Console\Command\Database;
use Spiral\Cycle\Console\Command\Migrate;

final class CommandBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        ConsoleBootloader::class,
        MigrationsBootloader::class,
    ];

    public function init(ConsoleBootloader $console, ContainerInterface $container): void
    {
        $this->configureExtensions($console, $container);
    }

    private function configureExtensions(ConsoleBootloader $console, ContainerInterface $container): void
    {
        if ($container->has(DatabaseProviderInterface::class)) {
            $this->configureDatabase($console);
        }

        if ($container->has(ORMInterface::class)) {
            $this->configureCycle($console, $container);
        }

        if ($container->has(Migrator::class)) {
            $this->configureMigrations($console);
        }
    }

    private function configureDatabase(ConsoleBootloader $console): void
    {
        $console->addCommand(Database\ListCommand::class);
        $console->addCommand(Database\TableCommand::class);
    }

    private function configureCycle(ConsoleBootloader $console, ContainerInterface $container): void
    {
        $console->addCommand(CycleOrm\UpdateCommand::class);
        $console->addCommand(CycleOrm\RenderCommand::class);

        $console->addUpdateSequence(
            'cycle',
            '<fg=magenta>[cycle]</fg=magenta> <fg=cyan>update Cycle schema...</fg=cyan>'
        );

        $console->addCommand(CycleOrm\SyncCommand::class);

        if ($container->has(Migrator::class)) {
            $console->addCommand(CycleOrm\MigrateCommand::class);
        }
    }

    private function configureMigrations(ConsoleBootloader $console): void
    {
        $console->addCommand(Migrate\InitCommand::class);
        $console->addCommand(Migrate\StatusCommand::class);
        $console->addCommand(Migrate\MigrateCommand::class);
        $console->addCommand(Migrate\RollbackCommand::class);
        $console->addCommand(Migrate\ReplayCommand::class);
    }
}
