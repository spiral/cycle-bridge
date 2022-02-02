<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Cycle\Validation\EntityChecker;

final class ValidationBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        \Spiral\Bootloader\Security\ValidationBootloader::class,
    ];

    public function boot(\Spiral\Bootloader\Security\ValidationBootloader $validation)
    {
        $validation->addChecker('entity', EntityChecker::class);
    }
}
