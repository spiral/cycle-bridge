<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\Scaffolder;

use Spiral\Cycle\Scaffolder\Declaration\MigrationDeclaration;
use Cycle\Migrations\Migrator;
use Spiral\Scaffolder\Command\AbstractCommand;
use Spiral\Scaffolder\Exception\ScaffolderException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrationCommand extends AbstractCommand
{
    protected const NAME = 'create:migration';
    protected const DESCRIPTION = 'Create migration declaration';
    protected const ARGUMENTS = [
        ['name', InputArgument::REQUIRED, 'Migration name'],
    ];
    protected const OPTIONS = [
        [
            'table',
            't',
            InputOption::VALUE_OPTIONAL,
            'Table to be created table',
        ],
        [
            'field',
            'f',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Create field in a format "name:type"',
        ],
        [
            'comment',
            null,
            InputOption::VALUE_OPTIONAL,
            'Optional comment to add as class header',
        ],
    ];

    /**
     * Create migration declaration.
     *
     * @throws ScaffolderException
     */
    public function perform(Migrator $migrator): int
    {
        /** @var MigrationDeclaration $declaration */
        $declaration = $this->createDeclaration(MigrationDeclaration::class);

        if (!empty($this->option('table'))) {
            $fields = [];
            foreach ($this->option('field') as $field) {
                if (!\str_contains($field, ':')) {
                    throw new ScaffolderException("Field definition must in 'name:type' form");
                }

                [$name, $type] = \explode(':', $field);
                $fields[$name] = $type;
            }

            $declaration->declareCreation((string)$this->option('table'), $fields);
        }

        $filename = $migrator->getRepository()->registerMigration(
            (string)$this->argument('name'),
            $declaration->getClass()->getName(),
            (string)$declaration->getFile(),
        );

        $this->writeln(
            "Declaration of '<info>{$declaration->getClass()->getName()}</info>' "
            . "has been successfully written into '<comment>{$filename}</comment>'.",
        );

        return self::SUCCESS;
    }
}
