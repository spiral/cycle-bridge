<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\ORM\Transaction\CommandGeneratorInterface;
use Mockery as m;
use Spiral\Core\Container;
use Spiral\Tests\BaseTest;

final class CycleOrmBootloaderRebindingTest extends BaseTest
{
    private CommandGeneratorInterface $commandGenerator;

    protected function setUp(): void
    {
        $this->commandGenerator = m::mock(CommandGeneratorInterface::class);
        $this->beforeBooting(function (Container $container): void {
            $container->bind(CommandGeneratorInterface::class, $this->commandGenerator);
        });
        parent::setUp();
    }

    public function testGetsOrmWithCustomCommandGenerator(): void
    {
        $this->assertSame($this->commandGenerator, $this->getOrm()->getCommandGenerator());
    }
}
