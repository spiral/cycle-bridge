<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\FactoryInterface;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\TransactionInterface;
use Spiral\Core\ConfigsInterface;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Tests\BaseTest;
use Spiral\Tests\ConfigAttribute;

final class CycleOrmBootloaderTest extends BaseTest
{
    public function testGetsOrm(): void
    {
        $this->assertContainerBoundAsSingleton(ORMInterface::class, ORM::class);
        $this->assertContainerBound(ORMInterface::class);
    }

    public function testGetsOrmFactory(): void
    {
        $this->assertContainerBound(FactoryInterface::class);
    }

    public function testGetsTransaction(): void
    {
        $this->assertContainerBound(TransactionInterface::class);
    }

    public function testGetsEntityManager(): void
    {
        $this->assertContainerBound(EntityManagerInterface::class);
    }

    #[ConfigAttribute(path: 'cycle.schema.collections', value: ['default' => 'test'])]
    public function testGetsCycleConfig(): void
    {
        $config = $this->getContainer()->get(CycleConfig::class);
        $configSource = $this->getConfig(CycleConfig::CONFIG);

        $this->assertSame('test', $config['schema']['collections']['default']);
        $this->assertSame('test', $configSource['schema']['collections']['default']);
    }

    public function testCycleConfigsSync(): void
    {
        $config = $this->getContainer()->get(CycleConfig::class)->toArray();
        $configSource = $this->getContainer()->get(ConfigsInterface::class)->getConfig(CycleConfig::CONFIG);

        $this->assertSame($config, $configSource);
    }

    public function testOrmWarmupDefaultConfigValue(): void
    {
        $config = $this->getContainer()->get(CycleConfig::class);

        $this->assertFalse($config->warmup());
    }
}
