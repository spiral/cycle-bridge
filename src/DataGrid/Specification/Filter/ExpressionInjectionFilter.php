<?php

declare(strict_types=1);

namespace Spiral\Cycle\DataGrid\Specification\Filter;

use Cycle\Database\Injection;

class ExpressionInjectionFilter extends InjectionFilter
{
    protected const INJECTION = Injection\Expression::class;
}
