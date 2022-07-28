<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\ORM\EntityManagerInterface;
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

    /** @dataProvider propertiesDataProvider */
    public function testBindProperties(string $expected, string $property): void
    {
        $registry = $this->getContainer()->get(PrototypeRegistry::class);

        $this->assertInstanceOf(
            $expected,
            $this->getContainer()->get($registry->resolveProperty($property)->type->name())
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

    public function propertiesDataProvider(): \Traversable
    {
        yield [DatabaseInterface::class, 'db'];
        yield [DatabaseProviderInterface::class, 'dbal'];
        yield [ORMInterface::class, 'orm'];
        yield [EntityManagerInterface::class, 'entityManager'];
    }
}
