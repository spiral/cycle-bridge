<?php

declare(strict_types=1);

namespace Spiral\Cycle\DataGrid\Writer;

use Cycle\Database\Driver\Postgres\PostgresDriver;
use Cycle\Database\Query\SelectQuery;
use Cycle\ORM\Select;
use Spiral\DataGrid\Compiler;
use Spiral\DataGrid\Exception\CompilerException;
use Spiral\DataGrid\Specification;
use Spiral\DataGrid\SpecificationInterface;
use Spiral\DataGrid\WriterInterface;

/**
 * Provides the ability to write into cycle/database SelectQuery and cycle/orm Select.
 */
class PostgresQueryWriter implements WriterInterface
{
    /**
     * @inheritDoc
     */
    public function write(mixed $source, SpecificationInterface $specification, Compiler $compiler): mixed
    {
        if (!$this->targetAcceptable($source)) {
            return null;
        }

        if ($specification instanceof Specification\Filter\Postgres\ILike) {
            return $source->where(
                $specification->getExpression(),
                'ILIKE',
                sprintf($specification->getPattern(), $this->fetchValue($specification->getValue()))
            );
        }

        return null;
    }

    /**
     * @param mixed $target
     * @return bool
     *
     * @psalm-suppress InternalMethod
     */
    protected function targetAcceptable($target): bool
    {
        if (
            class_exists(SelectQuery::class)
            && $target instanceof SelectQuery
            && $target->getDriver() instanceof PostgresDriver
        ) {
            return true;
        }

        if (
            $target instanceof Select
            && $target->buildQuery()->getDriver() instanceof PostgresDriver
        ) {
            return true;
        }

        return false;
    }

    /**
     * Fetch and assert that filter value is not expecting any user input.
     *
     * @param Specification\ValueInterface|mixed $value
     * @return mixed
     */
    protected function fetchValue($value)
    {
        if ($value instanceof Specification\ValueInterface) {
            throw new CompilerException('Value expects user input, none given');
        }

        return $value;
    }
}
