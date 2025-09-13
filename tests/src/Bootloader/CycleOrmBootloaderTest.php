<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\ORM\EntityManager;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\FactoryInterface;
use Cycle\ORM\Heap\HeapInterface;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Spiral\Boot\FinalizerInterface;
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
        $this->assertContainerBoundAsSingleton(FactoryInterface::class, \Cycle\ORM\Factory::class);
    }

    public function testGetsEntityManager(): void
    {
        $this->assertContainerBoundAsSingleton(EntityManagerInterface::class, EntityManager::class);
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

    public function testFinalizerShouldCleanHeapForLoop(): void
    {
        $orm = $this->mockContainer(ORMInterface::class);
        $em = $this->mockContainer(EntityManagerInterface::class);

        $orm->shouldReceive('getHeap')->once()->andReturn($heap = \Mockery::mock(HeapInterface::class));
        $heap->shouldReceive('clean')->once();
        $em->shouldReceive('clean')->once();

        $finalizer = $this->getContainer()->get(FinalizerInterface::class);

        $finalizer->finalize();
    }

    public function testFinalizerShouldNotCleanHeapAfterTerminating(): void
    {
        $orm = $this->mockContainer(ORMInterface::class);
        $em = $this->mockContainer(EntityManagerInterface::class);
        $orm->shouldReceive('getHeap')->never();
        $em->shouldReceive('clean')->never();

        $finalizer = $this->getContainer()->get(FinalizerInterface::class);

        $finalizer->finalize(true);
    }

    public function testContainerProvidesOptionsSingleton(): void
    {
        $opt1 = $this->getContainer()->get(\Cycle\ORM\Options::class);
        $opt2 = $this->getContainer()->get(\Cycle\ORM\Options::class);

        $this->assertInstanceOf(\Cycle\ORM\Options::class, $opt1);
        $this->assertSame($opt1, $opt2); // singleton
    }

    public function testOrmReceivesOptions(): void
    {
        $orm = $this->getContainer()->get(\Cycle\ORM\ORMInterface::class);
        $this->assertInstanceOf(\Cycle\ORM\ORM::class, $orm);

        $rp = new \ReflectionProperty($orm, 'options');
        $rp->setAccessible(true);
        $options = $rp->getValue($orm);

        $this->assertInstanceOf(\Cycle\ORM\Options::class, $options);
    }
}
