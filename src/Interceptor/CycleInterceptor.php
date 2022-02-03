<?php

declare(strict_types=1);

namespace Spiral\Cycle\Interceptor;

use Cycle\ORM\ORMInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\Exception\ControllerException;

/**
 * Automatically resolves cycle entities based on given parameter.
 */
class CycleInterceptor implements CoreInterceptorInterface
{
    // when only one entity is presented the default parameter will be checked
    protected const DEFAULT_PARAMETER = 'id';

    /**
     * [class:method][parameter] = resolved role
     * @var array<non-empty-string, array<non-empty-string, non-empty-string>>
     */
    private array $cache = [];

    public function __construct(
        protected ORMInterface $orm
    ) {
    }

    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        $entities = $this->getDeclaredEntities($controller, $action);

        $contextCandidates = [];
        foreach ($entities as $parameter => $role) {
            $value = $this->getParameter($parameter, $parameters, \count($entities) === 1);
            if ($value === null) {
                throw new ControllerException(
                    "Entity `{$parameter}` can not be found.",
                    ControllerException::NOT_FOUND
                );
            }

            if (\is_object($value)) {
                if ($this->orm->getHeap()->has($value)) {
                    $contextCandidates[] = $value;
                }

                // pre-filled
                continue;
            }

            $entity = $this->resolveEntity($role, $value);
            if ($entity === null) {
                throw new ControllerException(
                    "Entity `{$parameter}` can not be found.",
                    ControllerException::NOT_FOUND
                );
            }

            $parameters[$parameter] = $entity;
            $contextCandidates[] = $entity;
        }

        if (!isset($parameters['@context']) && \count($contextCandidates) === 1) {
            $parameters['@context'] = \current($contextCandidates);
        }

        return $core->callAction($controller, $action, $parameters);
    }

    protected function getParameter(string $role, array $parameters, bool $useDefault = false): mixed
    {
        if (!$useDefault) {
            return $parameters[$role] ?? null;
        }

        return $parameters[$role] ?? $parameters[self::DEFAULT_PARAMETER] ?? null;
    }

    protected function resolveEntity(string $role, mixed $parameter): ?object
    {
        return $this->orm->getRepository($role)->findByPK($parameter);
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    private function getDeclaredEntities(string $controller, string $action): array
    {
        $key = \sprintf('%s:%s', $controller, $action);
        if (\array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $this->cache[$key] = [];
        try {
            $method = new \ReflectionMethod($controller, $action);
        } catch (\ReflectionException) {
            return [];
        }

        foreach ($method->getParameters() as $parameter) {
            $class = $this->getParameterClass($parameter);

            if ($class === null) {
                continue;
            }

            if ($this->orm->getSchema()->defines($class->getName())) {
                $this->cache[$key][$parameter->getName()] = $this->orm->resolveRole($class->getName());
            }
        }

        return $this->cache[$key];
    }

    private function getParameterClass(\ReflectionParameter $parameter): ?\ReflectionClass
    {
        $type = $parameter->getType();

        if (!$type instanceof \ReflectionNamedType) {
            return null;
        }

        if ($type->isBuiltin()) {
            return null;
        }

        return new \ReflectionClass($type->getName());
    }
}
