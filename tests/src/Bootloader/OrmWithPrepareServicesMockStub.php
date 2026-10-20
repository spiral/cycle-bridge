<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\ORM\ORMInterface;

abstract class OrmWithPrepareServicesMockStub implements ORMInterface
{
    // `method_exists` must return true
    public function prepareServices(): void {}
}
