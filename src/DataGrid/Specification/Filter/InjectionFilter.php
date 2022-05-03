<?php

declare(strict_types=1);

namespace Spiral\Cycle\DataGrid\Specification\Filter;

use LogicException;
use Cycle\Database\Injection;
use Spiral\DataGrid\Specification\Filter\Between;
use Spiral\DataGrid\Specification\Filter\Expression;
use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\SpecificationInterface;

abstract class InjectionFilter implements FilterInterface
{
    protected const INJECTION = '';

    /** @var Between|Expression */
    private SpecificationInterface $expression;

    public function __construct(SpecificationInterface $expression)
    {
        if (!$expression instanceof Expression && !$expression instanceof Between) {
            throw new LogicException('Only expression filters allowed');
        }

        $this->expression = $expression;
    }

    public static function createFrom(InjectionFilter $injector, SpecificationInterface $expression): InjectionFilter
    {
        $clone = clone $injector;
        $clone->expression = $expression;

        return $clone;
    }

    public function getFilter(): SpecificationInterface
    {
        return $this->expression;
    }

    public function getInjection(): Injection\FragmentInterface
    {
        $injector = static::INJECTION;
        return new $injector($this->expression->getExpression());
    }

    public function withValue($value): ?SpecificationInterface
    {
        $filter = clone $this;
        $filter->expression = $filter->expression->withValue($value);

        return $filter->expression === null ? null : $filter;
    }

    public function getValue(): mixed
    {
        return $this->expression->getValue();
    }
}
