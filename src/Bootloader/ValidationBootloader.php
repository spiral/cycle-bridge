<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Cycle\Validation\EntityChecker;
use Spiral\Validator\Bootloader\ValidatorBootloader;

final class ValidationBootloader extends Bootloader
{
    public function boot(ValidatorBootloader $validation)
    {
        $validation->addChecker('entity', EntityChecker::class);
    }
}
