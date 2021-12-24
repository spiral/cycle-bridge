<?php

declare(strict_types=1);

namespace Spiral\Cycle\DataGrid\Specification\Sorter;

use Cycle\Database\Injection\Expression;

final class ExpressionInjectionSorter extends InjectionSorter
{
    protected const INJECTION = Expression::class;
}
