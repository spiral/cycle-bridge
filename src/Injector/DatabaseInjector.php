<?php

declare(strict_types=1);

namespace Spiral\Cycle\Injector;

use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseManager;
use Cycle\Database\Exception\DBALException;
use Spiral\Core\Container\InjectorInterface;

/**
 * @implements InjectorInterface<DatabaseInterface>
 */
final class DatabaseInjector implements InjectorInterface
{
    public function __construct(
        private readonly DatabaseManager $dm
    ) {
    }

    public function createInjection(\ReflectionClass $class, string $context = null): DatabaseInterface
    {
        // if context is empty default database will be returned
        try {
            return $this->dm->database($context);
        } catch (DBALException $e) {
            if ($context === null || !str_contains($e->getMessage(), ' no presets for ')) {
                throw $e;
            }
            // get default database
            return $this->dm->database();
        }
    }
}
