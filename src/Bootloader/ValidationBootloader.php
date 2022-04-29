<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Cycle\Validation\EntityChecker;

final class ValidationBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        \Spiral\Validation\Bootloader\ValidationBootloader::class,
    ];

    public function init(\Spiral\Validation\Bootloader\ValidationBootloader $validation)
    {
        $validation->addChecker('entity', EntityChecker::class);
    }
}
