<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\ORM\ORMInterface;
use Spiral\App\AppWithPrototype;
use Spiral\App\Repositories\UserRepository;
use Spiral\Prototype\PrototypeRegistry;
use Spiral\Tests\BaseTest;

final class PrototypeBootloaderTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app = $this->makeApp(static::ENV, AppWithPrototype::class);
    }

    public function testBindProperties(): void
    {
        $registry = $this->container->get(PrototypeRegistry::class);

        $this->assertInstanceOf(
            DatabaseInterface::class,
            $this->container->get($registry->resolveProperty('db')->type->name())
        );
        $this->assertInstanceOf(
            DatabaseProviderInterface::class,
            $this->container->get($registry->resolveProperty('dbal')->type->name())
        );
        $this->assertInstanceOf(
            ORMInterface::class,
            $this->container->get($registry->resolveProperty('orm')->type->name())
        );
    }

    public function testBindCycleEntities(): void
    {
        $registry = $this->container->get(PrototypeRegistry::class);

        $this->assertInstanceOf(
            UserRepository::class,
            $this->container->get($registry->resolveProperty('users')->type->name())
        );
    }
}
