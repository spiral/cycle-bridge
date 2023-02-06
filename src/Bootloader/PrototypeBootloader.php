<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\ORM;
use Doctrine\Inflector\Rules\English\InflectorFactory;
use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Prototype\Bootloader\PrototypeBootloader as BasePrototypeBootloader;

final class PrototypeBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        BasePrototypeBootloader::class,
    ];

    public function boot(BasePrototypeBootloader $prototype, ContainerInterface $container): void
    {
        $this->bindDatabase($prototype);
        $this->bindCycle($prototype, $container);
    }

    private function bindDatabase(BasePrototypeBootloader $prototype): void
    {
        if (\interface_exists(DatabaseInterface::class)) {
            $prototype->bindProperty('db', DatabaseInterface::class);
            $prototype->bindProperty('dbal', DatabaseProviderInterface::class);
        }
    }

    private function bindCycle(BasePrototypeBootloader $prototype, ContainerInterface $container): void
    {
        if (\interface_exists(ORM\ORMInterface::class)) {
            $prototype->bindProperty('orm', ORM\ORMInterface::class);
        }

        if (\interface_exists(ORM\EntityManagerInterface::class)) {
            $prototype->bindProperty('entityManager', ORM\EntityManagerInterface::class);
        }

        if (!$container->has(ORM\SchemaInterface::class)) {
            return;
        }

        /** @var ORM\SchemaInterface|null $schema */
        $schema = $container->get(ORM\SchemaInterface::class);
        if ($schema === null) {
            return;
        }

        $inflector = (new InflectorFactory())->build();
        foreach ($schema->getRoles() as $role) {
            $repository = $schema->define($role, ORM\SchemaInterface::REPOSITORY);
            if ($repository === ORM\Select\Repository::class || $repository === null) {
                // default repository can not be wired
                continue;
            }

            $prototype->bindProperty($inflector->pluralize($role), $repository);
        }
    }
}
