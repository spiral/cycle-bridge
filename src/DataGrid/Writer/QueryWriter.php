<?php

declare(strict_types=1);

namespace Spiral\Cycle\DataGrid\Writer;

use Cycle\Database\Injection\Parameter;
use Cycle\Database\Query\SelectQuery;
use Cycle\ORM\Select;
use Spiral\Cycle\DataGrid\Specification\Filter\InjectionFilter;
use Spiral\Cycle\DataGrid\Specification\Sorter\InjectionSorter;
use Spiral\DataGrid\Compiler;
use Spiral\DataGrid\Exception\CompilerException;
use Spiral\DataGrid\Specification;
use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\Specification\SorterInterface;
use Spiral\DataGrid\SpecificationInterface;
use Spiral\DataGrid\WriterInterface;

/**
 * Provides the ability to write into cycle/database SelectQuery and cycle/orm Select.
 */
class QueryWriter implements WriterInterface
{
    // Expression mapping
    protected const COMPARE_OPERATORS = [
        Specification\Filter\Lte::class => '<=',
        Specification\Filter\Lt::class => '<',
        Specification\Filter\Equals::class => '=',
        Specification\Filter\NotEquals::class => '!=',
        Specification\Filter\Gt::class => '>',
        Specification\Filter\Gte::class => '>=',
    ];

    protected const ARRAY_OPERATORS = [
        Specification\Filter\InArray::class => 'IN',
        Specification\Filter\NotInArray::class => 'NOT IN',
    ];

    // Sorter directions mapping
    protected const SORTER_DIRECTIONS = [
        Specification\Sorter\AscSorter::class => 'ASC',
        Specification\Sorter\DescSorter::class => 'DESC',
    ];

    public function write(mixed $source, SpecificationInterface $specification, Compiler $compiler): mixed
    {
        return match (true) {
            !$this->targetAcceptable($source) => null,
            $specification instanceof FilterInterface => $this->writeFilter($source, $specification, $compiler),
            $specification instanceof SorterInterface => $this->writeSorter($source, $specification, $compiler),
            $specification instanceof Specification\Pagination\Limit => $source->limit($specification->getValue()),
            $specification instanceof Specification\Pagination\Offset => $source->offset($specification->getValue()),
            default => null
        };
    }

    protected function writeFilter($source, FilterInterface $filter, Compiler $compiler): mixed
    {
        if ($filter instanceof Specification\Filter\All || $filter instanceof Specification\Filter\Map) {
            return $source->where(static function () use ($compiler, $filter, $source): void {
                $compiler->compile($source, ...$filter->getFilters());
            });
        }

        if ($filter instanceof Specification\Filter\Any) {
            return $source->where(static function () use ($compiler, $filter, $source): void {
                foreach ($filter->getFilters() as $subFilter) {
                    $source->orWhere(static function () use ($compiler, $subFilter, $source): void {
                        $compiler->compile($source, $subFilter);
                    });
                }
            });
        }

        if ($filter instanceof InjectionFilter) {
            $expression = $filter->getFilter();
            if ($expression instanceof Specification\Filter\Expression) {
                return $source->where(
                    $filter->getInjection(),
                    $this->getExpressionOperator($expression),
                    ...$this->getExpressionArgs($expression)
                );
            }
        }

        if ($filter instanceof Specification\Filter\Expression) {
            return $source->where(
                $filter->getExpression(),
                $this->getExpressionOperator($filter),
                ...$this->getExpressionArgs($filter)
            );
        }

        return null;
    }

    protected function getExpressionOperator(Specification\Filter\Expression $filter): string
    {
        if ($filter instanceof Specification\Filter\Like) {
            return 'LIKE';
        }

        if ($filter instanceof Specification\Filter\InArray || $filter instanceof Specification\Filter\NotInArray) {
            return static::ARRAY_OPERATORS[\get_class($filter)];
        }

        return static::COMPARE_OPERATORS[\get_class($filter)];
    }

    /**
     * @return array|Parameter[]|Specification\ValueInterface[]
     */
    protected function getExpressionArgs(Specification\Filter\Expression $filter): array
    {
        if ($filter instanceof Specification\Filter\Like) {
            return [\sprintf($filter->getPattern(), $this->fetchValue($filter->getValue()))];
        }

        if ($filter instanceof Specification\Filter\InArray || $filter instanceof Specification\Filter\NotInArray) {
            return [new Parameter($this->fetchValue($filter->getValue()))];
        }

        return [$this->fetchValue($filter->getValue())];
    }

    protected function writeSorter($source, SorterInterface $sorter, Compiler $compiler): mixed
    {
        if ($sorter instanceof Specification\Sorter\SorterSet) {
            foreach ($sorter->getSorters() as $subSorter) {
                $source = $compiler->compile($source, $subSorter);
            }

            return $source;
        }

        if (
            $sorter instanceof Specification\Sorter\AscSorter
            || $sorter instanceof Specification\Sorter\DescSorter
        ) {
            $direction = static::SORTER_DIRECTIONS[\get_class($sorter)];
            foreach ($sorter->getExpressions() as $expression) {
                $source = $source->orderBy($expression, $direction);
            }

            return $source;
        }

        if ($sorter instanceof InjectionSorter) {
            $direction = static::SORTER_DIRECTIONS[\get_class($sorter)] ?? 'ASC';
            foreach ($sorter->getInjections() as $injection) {
                $source = $source->orderBy($injection, $direction);
            }

            return $source;
        }

        return null;
    }

    /**
     * Fetch and assert that filter value is not expecting any user input.
     */
    protected function fetchValue(mixed $value): mixed
    {
        if ($value instanceof Specification\ValueInterface) {
            throw new CompilerException('Value expects user input, none given');
        }

        return $value;
    }

    protected function targetAcceptable(mixed $target): bool
    {
        if (\class_exists(SelectQuery::class) && $target instanceof SelectQuery) {
            return true;
        }

        if (\class_exists(Select::class) && $target instanceof Select) {
            return true;
        }

        return false;
    }
}
