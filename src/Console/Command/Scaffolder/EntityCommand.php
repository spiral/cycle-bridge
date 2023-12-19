<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\Scaffolder;

use Spiral\Console\Console;
use Spiral\Cycle\Scaffolder\Declaration\Entity\AnnotatedDeclaration;
use Spiral\Cycle\Scaffolder\Declaration\RepositoryDeclaration;
use Spiral\Reactor\Partial\Visibility;
use Spiral\Scaffolder\Command\AbstractCommand;
use Spiral\Scaffolder\Config\ScaffolderConfig;
use Spiral\Scaffolder\Exception\ScaffolderException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

use function Spiral\Scaffolder\trimPostfix;

class EntityCommand extends AbstractCommand
{
    protected const NAME = 'create:entity';
    protected const DESCRIPTION = 'Create entity declaration';
    protected const ARGUMENTS = [
        ['name', InputArgument::REQUIRED, 'Entity name'],
    ];
    protected const OPTIONS = [
        [
            'role',
            'r',
            InputOption::VALUE_OPTIONAL,
            'Entity role, defaults to lowercase class name without a namespace',
        ],
        [
            'mapper',
            'm',
            InputOption::VALUE_OPTIONAL,
            'Mapper class name, defaults to Cycle\ORM\Mapper\Mapper',
        ],
        [
            'repository',
            'e',
            InputOption::VALUE_NONE,
            'Repository class to represent read operations for an entity, defaults to Cycle\ORM\Select\Repository',
        ],
        [
            'table',
            't',
            InputOption::VALUE_OPTIONAL,
            'Entity source table, defaults to plural form of entity role',
        ],
        [
            'database',
            'd',
            InputOption::VALUE_OPTIONAL,
            'Database name, defaults to null (default database)',
        ],
        [
            'accessibility',
            'a',
            InputOption::VALUE_OPTIONAL,
            'Accessibility accessor (public, protected, private)',
            'public',
        ],
        [
            'inflection',
            'i',
            InputOption::VALUE_OPTIONAL,
            'Column name inflection, allowed values: tableize (t), camelize (c)',
            'tableize',
        ],
        [
            'field',
            'f',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Add field in a format "name:type"',
        ],
        [
            'comment',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Optional comment to add as class header',
        ],
    ];

    /**
     * Create entity declaration.
     *
     * @throws Throwable
     */
    public function perform(Console $console, ScaffolderConfig $config): int
    {
        $accessibility = (string)$this->option('accessibility');
        $this->validateAccessibility($accessibility);

        /** @var AnnotatedDeclaration $declaration */
        $declaration = $this->createDeclaration(AnnotatedDeclaration::class);

        $repository = trimPostfix((string)$this->argument('name'), 'repository');
        if ($this->option('repository')) {
            $repositoryClass = $config->className(RepositoryDeclaration::TYPE, $repository);
            $repositoryNamespace = $config->classNamespace(RepositoryDeclaration::TYPE, $repository);
            $declaration->setRepository("\\$repositoryNamespace\\$repositoryClass");
        }

        $declaration->setRole((string)$this->option('role'));
        $declaration->setMapper((string)$this->option('mapper'));
        $declaration->setTable((string)$this->option('table'));
        $declaration->setDatabase((string)$this->option('database'));
        $declaration->setInflection((string)$this->option('inflection'));

        foreach ($this->option('field') as $field) {
            if (!\str_contains($field, ':')) {
                throw new ScaffolderException("Field definition must in 'name:type' or 'name:type' form");
            }

            $parts = \explode(':', $field);
            [$name, $type] = $parts;

            $declaration->addField($name, $accessibility, $type);
        }

        $declaration->declareSchema();

        $this->writeDeclaration($declaration);

        if ($this->option('repository')) {
            $console->run('create:repository', [
                'name' => !empty($repository) ? $repository : $this->argument('name'),
            ]);
        }

        return self::SUCCESS;
    }

    protected function declarationClass(string $element): string
    {
        return $this->config->declarationOptions($element)[(string)$this->argument('format')];
    }

    private function validateAccessibility(string $accessibility): void
    {
        if (
            !\in_array($accessibility, [
                Visibility::PUBLIC->value,
                Visibility::PROTECTED->value,
                Visibility::PRIVATE->value,
            ], true)
        ) {
            throw new ScaffolderException("Invalid accessibility value `$accessibility`");
        }
    }
}
