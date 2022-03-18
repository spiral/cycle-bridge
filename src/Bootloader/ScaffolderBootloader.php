<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Cycle\Scaffolder\Declaration;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Scaffolder\Bootloader\ScaffolderBootloader as BaseScaffolderBootloader;

class ScaffolderBootloader extends Bootloader
{
    public const DEPENDENCIES = [
        ConsoleBootloader::class,
        BaseScaffolderBootloader::class
    ];

    public function boot(BaseScaffolderBootloader $scaffolder): void
    {
        $scaffolder->addDeclaration('migration', [
            'namespace' => '',
            'postfix'   => 'Migration',
            'class'     => Declaration\MigrationDeclaration::class,
        ]);

        $scaffolder->addDeclaration('entity', [
            'namespace' => 'Database',
            'postfix'   => '',
            'options'   => [
                'annotated' => Declaration\Entity\AnnotatedDeclaration::class,
            ],
        ]);

        $scaffolder->addDeclaration('repository', [
            'namespace' => 'Repository',
            'postfix'   => 'Repository',
            'class'     => Declaration\RepositoryDeclaration::class,
        ]);
    }
}
