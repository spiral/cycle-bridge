<?php

declare(strict_types=1);

namespace Spiral\Cycle\DataGrid\Specification\Sorter;

use Cycle\Database\Injection\Fragment;

final class FragmentInjectionSorter extends InjectionSorter
{
    protected const INJECTION = Fragment::class;
}
