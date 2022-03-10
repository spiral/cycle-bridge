<?php

declare(strict_types=1);

namespace Spiral\App\Injector;

use Cycle\Database\Database;
use Cycle\Database\DatabaseInterface;

final class DatabaseInjectorAliasResolverTester
{
    public function __construct(
        public DatabaseInterface $dbRuntime,
        public DatabaseInterface $dbOther,
        public Database          $dbSqlite,
        public Database          $dbPostgres,
    ) {
    }
}
