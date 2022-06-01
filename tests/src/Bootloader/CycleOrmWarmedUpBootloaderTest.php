<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\ORM\Heap\HeapInterface;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Mockery as m;
use Spiral\Core\Container;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Tests\BaseTest;

abstract class OrmWithPrepareServicesMockStub implements ORMInterface
{
    // `method_exists` must return true
    public function prepareServices(): void
    {
    }
}

final class CycleOrmWarmedUpBootloaderTest extends BaseTest
{
    protected function setUp(): void
    {
        $this->beforeBooting(
            \Closure::bind(static function (CycleConfig $config) {
                $config->config['warmup'] = true;
            }, null, CycleConfig::class)
        );

        $orm = m::mock(OrmWithPrepareServicesMockStub::class);
        $orm->shouldAllowMockingMethod('prepareServices');
        $orm->shouldReceive('prepareServices')->once();
        $orm->shouldReceive('getHeap')
            ->andReturnUsing(static function (): HeapInterface {
                $heap = m::mock(HeapInterface::class);
                $heap->shouldReceive('clean');
                return $heap;
            });

        $this->beforeBooting(static function (Container $container) use ($orm) {
            $container->bindSingleton(ORMInterface::class, $orm);
            $container->bindSingleton(ORM::class, $orm);
        });

        parent::setUp();
    }

    public function testOrmWarmupConfig(): void
    {
        $config = $this->getContainer()->get(CycleConfig::class);

        $this->assertTrue($config->warmup());
    }
}
