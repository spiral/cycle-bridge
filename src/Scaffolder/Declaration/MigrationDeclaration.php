<?php

declare(strict_types=1);

namespace Spiral\Cycle\Scaffolder\Declaration;

use Cycle\Migrations\Migration;
use Spiral\Scaffolder\Declaration\AbstractDeclaration;

/**
 * Migration declaration
 */
class MigrationDeclaration extends AbstractDeclaration
{
    public const TYPE = 'migration';

    /**
     * Declare table creation with specific set of columns
     */
    public function declareCreation(string $table, array $columns): void
    {
        $body = "\$this->table('{$table}')";
        foreach ($columns as $name => $type) {
            $body .= "->addColumn('{$name}', '{$type}')";
        }
        $body .= '->create();';

        $this->class->getMethod('up')->addBody($body);

        $this->class->getMethod('down')->addBody("\$this->table('{$table}')->drop();");
    }

    public function declare(): void
    {
        $this->namespace->addUse(Migration::class);

        $this->class->setExtends(Migration::class);

        $this->class
            ->addMethod('up')
            ->setPublic()
            ->setReturnType('void')
            ->setComment('Create tables, add columns or insert data here');

        $this->class
            ->addMethod('down')
            ->setPublic()
            ->setReturnType('void')
            ->setComment('Drop created, columns and etc here');
    }
}
