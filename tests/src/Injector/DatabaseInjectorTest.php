<?php

declare(strict_types=1);

namespace Spiral\Tests\Injector;

use Cycle\Database\Database;
use Cycle\Database\DatabaseInterface;
use Spiral\App\Injector\DatabaseInjectorAliasResolverTester;
use Spiral\Tests\BaseTest;

final class DatabaseInjectorTest extends BaseTest
{
    public function testInjectRepository(): void
    {
        self::assertInstanceOf(DatabaseInterface::class, $this->container->get(DatabaseInterface::class));
        self::assertInstanceOf(Database::class, $this->container->get(Database::class));
    }

    public function testAlias(): void
    {
        $obj = $this->container->get(DatabaseInjectorAliasResolverTester::class);
        self::assertInstanceOf(Database::class, $obj->dbRuntime);
        self::assertInstanceOf(Database::class, $obj->dbOther);
        self::assertSame($obj->dbRuntime->getName(), $obj->dbSqlite->getName());
        self::assertSame($obj->dbOther->getName(), $obj->dbPostgres->getName());
    }
}
