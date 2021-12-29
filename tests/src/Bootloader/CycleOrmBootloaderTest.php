<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\FactoryInterface;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\TransactionInterface;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Tests\TestCase;

final class CycleOrmBootloaderTest extends TestCase
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
