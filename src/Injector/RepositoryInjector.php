<?php

declare(strict_types=1);

namespace Spiral\Cycle\Injector;

use Cycle\ORM\Exception\ORMException;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Select;
use ReflectionClass;
use Spiral\Core\Container\InjectorInterface;

/**
 * @implements InjectorInterface<RepositoryInterface>
 */
final class RepositoryInjector implements InjectorInterface
{
    public function __construct(
        private readonly ORMInterface $orm
    ) {
    }

    public function createInjection(ReflectionClass $class, string $context = null): RepositoryInterface
    {
        $schema = $this->orm->getSchema();

        foreach ($schema->getRoles() as $role) {
            $repository = $schema->define($role, SchemaInterface::REPOSITORY);

            if ($repository !== Select\Repository::class) {
                if ($repository === $class->getName() || \in_array($repository, $class->getInterfaceNames(), true)) {
                    return $this->orm->getRepository($role);
                }
            }
        }

        throw new ORMException(
            \sprintf('Unable to find Entity role for repository %s', $class->getName())
        );
    }
}
