<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\Database;

use Cycle\Database\Database;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\Database\Driver\DriverInterface;
use Cycle\Database\Exception\DBALException;
use Cycle\Database\Injection\FragmentInterface;
use Cycle\Database\Query\QueryParameters;
use Cycle\Database\Schema\AbstractColumn;
use Cycle\Database\Schema\AbstractTable;
use Cycle\Database\Table;
use Spiral\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class TableCommand extends Command
{
    protected const NAME = 'db:table';
    protected const DESCRIPTION = 'Describe table schema of specific database';
    protected const ARGUMENTS = [
        ['table', InputArgument::REQUIRED, 'Table name'],
    ];
    protected const OPTIONS = [
        ['database', 'db', InputOption::VALUE_OPTIONAL, 'Source database', 'default'],
    ];

    private const SKIP = '<comment>---</comment>';

    public function perform(DatabaseProviderInterface $dbal): int
    {
        $database = $dbal->database($this->option('database'));
        /** @var Table $table */
        $table = $database->table($this->argument('table'));
        $schema = $table->getSchema();

        if (! $schema->exists()) {
            throw new DBALException(
                "Table {$database->getName()}.{$this->argument('table')} does not exists."
            );
        }

        $this->sprintf(
            "\n<fg=cyan>Columns of </fg=cyan><comment>%s.%s</comment>:\n",
            $database->getName(),
            $this->argument('table')
        );

        $this->describeColumns($schema);

        if (! empty($indexes = $schema->getIndexes())) {
            $this->describeIndexes($database, $indexes);
        }

        if (! empty($foreignKeys = $schema->getForeignKeys())) {
            $this->describeForeignKeys($database, $foreignKeys);
        }

        $this->write("\n");

        return self::SUCCESS;
    }

    protected function describeColumns(AbstractTable $schema): void
    {
        $columnsTable = $this->table(
            [
                'Column:',
                'Database Type:',
                'Abstract Type:',
                'PHP Type:',
                'Default Value:',
            ]
        );

        foreach ($schema->getColumns() as $column) {
            $name = $column->getName();

            if (in_array($column->getName(), $schema->getPrimaryKeys(), true)) {
                $name = "<fg=magenta>{$name}</fg=magenta>";
            }

            $defaultValue = $this->describeDefaultValue($column, $schema->getDriver());

            $columnsTable->addRow(
                [
                    $name,
                    $this->describeType($column),
                    $this->describeAbstractType($column),
                    $column->getType(),
                    $defaultValue ?? self::SKIP,
                ]
            );
        }

        $columnsTable->render();
    }

    protected function describeIndexes(Database $database, array $indexes): void
    {
        $this->sprintf(
            "\n<fg=cyan>Indexes of </fg=cyan><comment>%s.%s</comment>:\n",
            $database->getName(),
            $this->argument('table')
        );

        $indexesTable = $this->table(['Name:', 'Type:', 'Columns:']);
        foreach ($indexes as $index) {
            $indexesTable->addRow(
                [
                    $index->getName(),
                    $index->isUnique() ? 'UNIQUE INDEX' : 'INDEX',
                    implode(', ', $index->getColumns()),
                ]
            );
        }

        $indexesTable->render();
    }

    protected function describeForeignKeys(Database $database, array $foreignKeys): void
    {
        $this->sprintf(
            "\n<fg=cyan>Foreign Keys of </fg=cyan><comment>%s.%s</comment>:\n",
            $database->getName(),
            $this->argument('table')
        );
        $foreignTable = $this->table(
            [
                'Name:',
                'Column:',
                'Foreign Table:',
                'Foreign Column:',
                'On Delete:',
                'On Update:',
            ]
        );

        foreach ($foreignKeys as $reference) {
            $foreignTable->addRow(
                [
                    $reference->getName(),
                    implode(', ', $reference->getColumns()),
                    $reference->getForeignTable(),
                    implode(', ', $reference->getForeignKeys()),
                    $reference->getDeleteRule(),
                    $reference->getUpdateRule(),
                ]
            );
        }

        $foreignTable->render();
    }

    protected function describeDefaultValue(AbstractColumn $column, DriverInterface $driver)
    {
        $defaultValue = $column->getDefaultValue();

        if ($defaultValue instanceof FragmentInterface) {
            $value = $driver->getQueryCompiler()->compile(new QueryParameters(), '', $defaultValue);

            return "<info>{$value}</info>";
        }

        if ($defaultValue instanceof \DateTimeInterface) {
            $defaultValue = $defaultValue->format('c');
        }

        return $defaultValue;
    }

    private function describeType(AbstractColumn $column): string
    {
        $type = $column->getType();

        $abstractType = $column->getAbstractType();

        if ($column->getSize()) {
            $type .= " ({$column->getSize()})";
        }

        if ($abstractType === 'decimal') {
            $type .= " ({$column->getPrecision()}, {$column->getScale()})";
        }

        return $type;
    }

    private function describeAbstractType(AbstractColumn $column): string
    {
        $abstractType = $column->getAbstractType();

        if (in_array($abstractType, ['primary', 'bigPrimary'])) {
            $abstractType = "<fg=magenta>{$abstractType}</fg=magenta>";
        }

        return $abstractType;
    }
}

