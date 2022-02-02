<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\FactoryInterface;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Transaction\CommandGeneratorInterface;
use Cycle\ORM\TransactionInterface;
use Mockery as m;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Tests\BaseTest;

final class CycleOrmBootloaderTest extends BaseTest
{
    public function testGetsOrm(): void
    {
        $this->assertInstanceOf(
            ORMInterface::class,
            $this->app->get(ORMInterface::class)
        );

        $this->assertInstanceOf(
            ORMInterface::class,
            $this->app->get(ORM::class)
        );
    }

    public function testGetsOrmWithCustomCommandGenerator(): void
    {
        $this->app->getContainer()->bind(
            CommandGeneratorInterface::class,
            $commandGenerator = m::mock(CommandGeneratorInterface::class)
        );

        $this->assertInstanceOf(
            ORMInterface::class,
            $orm = $this->app->get(ORMInterface::class)
        );

        $this->assertSame(
            $commandGenerator,
            $orm->getCommandGenerator()
        );
    }

    public function testGetsOrmFactory(): void
    {
        $this->assertInstanceOf(
            FactoryInterface::class,
            $this->app->get(FactoryInterface::class)
        );
    }

    public function testGetsTransaction(): void
    {
        $this->assertInstanceOf(
            TransactionInterface::class,
            $this->app->get(TransactionInterface::class)
        );
    }

    public function testGetsEntityManager(): void
    {
        $this->assertInstanceOf(
            EntityManagerInterface::class,
            $this->app->get(EntityManagerInterface::class)
        );
    }

    public function testGetsCycleConfig(): void
    {
        $this->updateConfig('cycle.schema.collections', [
            'default' => 'test',
        ]);

        $config = $this->app->get(CycleConfig::class);

        $this->assertSame('test', $config['schema']['collections']['default']);
    }
}
