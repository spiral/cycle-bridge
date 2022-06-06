<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\Scaffolder;

use Spiral\Cycle\Scaffolder\Declaration\RepositoryDeclaration;
use Spiral\Scaffolder\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RepositoryCommand extends AbstractCommand
{
    protected const NAME        = 'create:repository';
    protected const DESCRIPTION = 'Create repository declaration';
    protected const ARGUMENTS   = [
        ['name', InputArgument::REQUIRED, 'Repository name'],
    ];
    protected const OPTIONS     = [
        [
            'comment',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Optional comment to add as class header',
        ],
    ];

    /**
     * Create repository declaration.
     */
    public function perform(): int
    {
        /** @var RepositoryDeclaration $declaration */
        $declaration = $this->createDeclaration(RepositoryDeclaration::class);

        $this->writeDeclaration($declaration);

        return self::SUCCESS;
    }
}
