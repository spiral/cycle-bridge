<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\ORM\ORMInterface;
use Spiral\App\Repositories\UserRepository;
use Spiral\Cycle\Bootloader\PrototypeBootloader;
use Spiral\Prototype\PrototypeRegistry;
use Spiral\Tests\BaseTest;

final class PrototypeBootloaderTest extends BaseTest
{
    public function defineBootloaders(): array
    {
        return \array_merge(parent::defineBootloaders(), [
            PrototypeBootloader::class,
        ]);
    }

    public function testBindProperties(): void
    {
        $registry = $this->getContainer()->get(PrototypeRegistry::class);

        $this->assertInstanceOf(
            DatabaseInterface::class,
            $this->getContainer()->get($registry->resolveProperty('db')->type->name())
        );
        $this->assertInstanceOf(
            DatabaseProviderInterface::class,
            $this->getContainer()->get($registry->resolveProperty('dbal')->type->name())
        );
        $this->assertInstanceOf(
            ORMInterface::class,
            $this->getContainer()->get($registry->resolveProperty('orm')->type->name())
        );
    }

    public function testBindCycleEntities(): void
    {
        $registry = $this->getContainer()->get(PrototypeRegistry::class);

        $this->assertInstanceOf(
            UserRepository::class,
            $this->getContainer()->get($registry->resolveProperty('users')->type->name())
        );
    }
}
