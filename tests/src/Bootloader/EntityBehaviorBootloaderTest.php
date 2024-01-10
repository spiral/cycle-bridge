<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\ORM\Entity\Behavior\EventDrivenCommandGenerator;
use Cycle\ORM\Transaction\CommandGeneratorInterface;
use Spiral\Tests\BaseTest;

final class EntityBehaviorBootloaderTest extends BaseTest
{
    public function testThatEventDrivenCommandGeneratorIsBound(): void
    {
        $this->assertContainerBound(CommandGeneratorInterface::class, EventDrivenCommandGenerator::class);
    }
}
