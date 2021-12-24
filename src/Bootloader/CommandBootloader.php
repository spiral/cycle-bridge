<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Cycle\Database\DatabaseProviderInterface;
use Cycle\Migrations\Migrator;
use Cycle\ORM\ORMInterface;
use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\ConsoleBootloader;
use Spiral\Command\Encrypter;
use Spiral\Command\GRPC;
use Spiral\Command\Router;
use Spiral\Command\Translator;
use Spiral\Command\Views;
use Spiral\Console;
use Spiral\Console\Sequence\RuntimeDirectory;
use Spiral\Core\Container;
use Spiral\Cycle\Console\Command\CycleOrm;
use Spiral\Cycle\Console\Command\Database;
use Spiral\Cycle\Console\Command\Migrate;
use Spiral\Encrypter\EncryptionInterface;
use Spiral\Files\FilesInterface;
use Spiral\Router\RouterInterface;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\TranslatorInterface;
use Spiral\Views\ViewsInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CommandBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        ConsoleBootloader::class,
        MigrationsBootloader::class,
    ];

    public function boot(ConsoleBootloader $console, Container $container): void
    {
        $console->addCommand(Console\Command\ConfigureCommand::class);
        $console->addCommand(Console\Command\UpdateCommand::class);

        $console->addConfigureSequence(
            [RuntimeDirectory::class, 'ensure'],
            '<fg=magenta>[runtime]</fg=magenta> <fg=cyan>verify `runtime` directory access</fg=cyan>'
        );

        $this->configureExtensions($console, $container);
    }

    private function configureExtensions(ConsoleBootloader $console, Container $container): void
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
