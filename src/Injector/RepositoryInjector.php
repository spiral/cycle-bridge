<?php

declare(strict_types=1);

namespace Spiral\Cycle\Injector;

use Cycle\ORM\Exception\ORMException;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;
use Cycle\ORM\Schema;
use Cycle\ORM\Select;
use ReflectionClass;
use Spiral\Core\Container\InjectorInterface;

final class RepositoryInjector implements InjectorInterface
{
    public function __construct(
        private ORMInterface $orm
    ) {
    }

    public function createInjection(ReflectionClass $class, string $context = null): RepositoryInterface
    {
        $schema = $this->orm->getSchema();

        foreach ($schema->getRoles() as $role) {
            $repository = $schema->define($role, Schema::REPOSITORY);

            if ($repository !== Select\Repository::class && $repository === $class->getName()) {
                return $this->orm->getRepository($role);
            }
        }

        throw new ORMException(sprintf('Unable to find Entity role for repository %s', $class->getName()));
    }
}
