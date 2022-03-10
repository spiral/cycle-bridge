<?php

declare(strict_types=1);

namespace Spiral\Cycle\Injector;

use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseManager;
use Spiral\Core\Container\InjectorInterface;

final class DatabaseInjector implements InjectorInterface
{
    public function __construct(
        private DatabaseManager $dm
    ) {
    }

    public function createInjection(\ReflectionClass $class, string $context = null): DatabaseInterface
    {
        // if context is empty default database will be returned
        return $this->dm->database($context);
    }
}
