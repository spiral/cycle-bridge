<?php

declare(strict_types=1);

namespace Spiral\Cycle\DataGrid\Writer;

use Cycle\Database\Injection\Parameter;
use Spiral\Cycle\DataGrid\Specification\Filter\InjectionFilter;
use Spiral\DataGrid\Compiler;
use Spiral\DataGrid\Specification\Filter;
use Spiral\DataGrid\SpecificationInterface;
use Spiral\DataGrid\WriterInterface;

class BetweenWriter implements WriterInterface
{
    private bool $asOriginal;

    public function __construct(bool $asOriginal = false)
    {
        $this->asOriginal = $asOriginal;
    }

    public function write(mixed $source, SpecificationInterface $specification, Compiler $compiler): mixed
    {
        if ($specification instanceof Filter\Between || $specification instanceof Filter\ValueBetween) {
            $filters = $specification->getFilters($this->asOriginal);
            if (\count($filters) > 1) {
                return $source->where(static function () use ($compiler, $source, $filters): void {
                    $compiler->compile($source, ...$filters);
                });
            }
        }

        if ($specification instanceof InjectionFilter) {
            $expression = $specification->getFilter();
            if ($expression instanceof Filter\Between) {
                $filters = $expression->getFilters($this->asOriginal);
                if (\count($filters) > 1) {
                    $filters = \array_map(
                        static fn (SpecificationInterface $filter): InjectionFilter =>
                            InjectionFilter::createFrom($specification, $filter),
                        $filters
                    );

                    return $source->where(
                        static function () use ($compiler, $source, $filters): void {
                            $compiler->compile($source, ...$filters);
                        }
                    );
                }

                return $source->where(
                    $specification->getInjection(),
                    'BETWEEN',
                    ...$specification->getValue()
                );
            }
        }

        if ($specification instanceof Filter\Between) {
            return $source->where(
                $specification->getExpression(),
                'BETWEEN',
                ...$specification->getValue()
            );
        }

        if ($specification instanceof Filter\ValueBetween) {
            return $source->where(
                new Parameter($specification->getValue()),
                'BETWEEN',
                ...$specification->getExpression()
            );
        }

        return null;
    }
}
