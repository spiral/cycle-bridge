<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Cycle\Migrations\Migrator;
use Cycle\ORM\ORMInterface;
use Psr\Container\ContainerInterface;
use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Cycle\Console\Command\Scaffolder;
use Spiral\Cycle\Scaffolder\Declaration;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Scaffolder\Bootloader\ScaffolderBootloader as BaseScaffolderBootloader;

final class ScaffolderBootloader extends Bootloader
{
    public const DEPENDENCIES = [
        ConsoleBootloader::class,
        BaseScaffolderBootloader::class
    ];

    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public function init(BaseScaffolderBootloader $scaffolder, ConsoleBootloader $console): void
    {
        $this->configureCommands($console);
        $this->configureDeclarations($scaffolder);
    }

    private function configureCommands(ConsoleBootloader $console): void
    {
        if ($this->container->has(Migrator::class)) {
            $console->addCommand(Scaffolder\MigrationCommand::class);
        }

        if ($this->container->has(ORMInterface::class)) {
            $console->addCommand(Scaffolder\EntityCommand::class);
            $console->addCommand(Scaffolder\RepositoryCommand::class);
        }
    }

    private function configureDeclarations(BaseScaffolderBootloader $scaffolder): void
    {
        $scaffolder->addDeclaration(Declaration\MigrationDeclaration::TYPE, [
            'namespace' => '',
            'postfix'   => 'Migration',
            'class'     => Declaration\MigrationDeclaration::class,
        ]);

        $scaffolder->addDeclaration(Declaration\Entity\AnnotatedDeclaration::TYPE, [
            'namespace' => 'Database',
            'postfix'   => '',
            'options'   => [
                'annotated' => Declaration\Entity\AnnotatedDeclaration::class,
            ],
        ]);

        $scaffolder->addDeclaration(Declaration\RepositoryDeclaration::TYPE, [
            'namespace' => 'Repository',
            'postfix'   => 'Repository',
            'class'     => Declaration\RepositoryDeclaration::class,
        ]);
    }
}
