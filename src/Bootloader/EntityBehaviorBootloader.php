<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Cycle\ORM\Entity\Behavior\EventDrivenCommandGenerator;
use Cycle\ORM\Transaction\CommandGeneratorInterface;
use Spiral\Boot\Bootloader\Bootloader;

final class EntityBehaviorBootloader extends Bootloader
{
    public function defineBindings(): array
    {
        return [
            CommandGeneratorInterface::class => EventDrivenCommandGenerator::class,
        ];
    }
}
