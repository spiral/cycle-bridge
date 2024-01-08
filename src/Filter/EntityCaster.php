<?php

declare(strict_types=1);

namespace Spiral\Cycle\Filter;

use Cycle\ORM\ORMInterface;
use Psr\Container\ContainerInterface;
use Spiral\Exceptions\ExceptionReporterInterface;
use Spiral\Filters\Exception\SetterException;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\Mapper\CasterInterface;

final class EntityCaster implements CasterInterface
{
    /**
     * @var array<class-string, non-empty-string>
     */
    private static array $cache = [];
    private ?ORMInterface $orm = null;

    public function __construct(
        protected readonly ContainerInterface $container,
        protected readonly ExceptionReporterInterface $reporter,
    ) {
    }

    public function supports(\ReflectionNamedType $type): bool
    {
        if ($type->isBuiltin()) {
            return false;
        }

        return $this->getOrm()->getSchema()->defines($type->getName());
    }

    public function setValue(FilterInterface $filter, \ReflectionProperty $property, mixed $value): void
    {
        try {
            $role = $this->resolveRole($property->getType());
            $object = $this->getOrm()->getRepository($role)->findByPK($value);
        } catch (\Throwable $e) {
            $this->reporter->report($e);
            throw new SetterException(previous: $e);
        }

        if ($object === null && !$property->getType()->allowsNull()) {
            throw new SetterException(
                message: \sprintf('Unable to find entity `%s` by primary key "%s"', $role, $value),
            );
        }

        $property->setValue($filter, $object);
    }

    private function resolveRole(\ReflectionNamedType $type): string
    {
        if (isset(self::$cache[$type->getName()])) {
            return self::$cache[$type->getName()];
        }

        $role = $this->getOrm()->resolveRole($type->getName());
        self::$cache[$type->getName()] = $role;

        return $role;
    }

    private function getOrm(): ORMInterface
    {
        if ($this->orm === null) {
            $this->orm = $this->container->get(ORMInterface::class);
        }

        return $this->orm;
    }
}
