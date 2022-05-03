<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Cycle\DataGrid\Writer\BetweenWriter;
use Spiral\Cycle\DataGrid\Writer\QueryWriter;
use Spiral\DataGrid\Bootloader\GridBootloader;

final class DataGridBootloader extends Bootloader
{
    public function init(GridBootloader $grid): void
    {
        $grid->addWriter(QueryWriter::class);
        $grid->addWriter(BetweenWriter::class);
    }
}
