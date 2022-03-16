<?php

declare(strict_types=1);

namespace Spiral\Tests\Injector;

use Cycle\Database\Database;
use Cycle\Database\DatabaseInterface;
use Spiral\App\Injector\DatabaseInjectorAliasResolverTester;
use Spiral\Core\Container;
use Spiral\Tests\BaseTest;

final class DatabaseInjectorTest extends BaseTest
{
    public function testInjectRepository(): void
    {
        // $this->assertTrue($this->getContainer()->has(DatabaseInterface::class));
        // $this->assertContainerBoundAsSingleton(DatabaseInterface::class, Database::class);
        $this->assertInstanceOf(DatabaseInterface::class, $this->getContainer()->get(DatabaseInterface::class));
        $this->assertInstanceOf(Database::class, $this->getContainer()->get(Database::class));
    }

    public function testAlias(): void
    {
        $obj = $this->getContainer()->get(DatabaseInjectorAliasResolverTester::class);
        $this->assertInstanceOf(Database::class, $obj->dbRuntime);
        $this->assertInstanceOf(Database::class, $obj->dbOther);
        $this->assertSame($obj->dbRuntime, $obj->dbSqlite);
        $this->assertSame($obj->dbOther, $obj->dbPostgres);
    }

    public function testUseUndeclaredContext(): void
    {
        $c = $this->getContainer();

        $database1 = $c->invoke([DatabaseInjectorAliasResolverTester::class, 'getDefaultDatabase']);
        $database2 = $c->get(DatabaseInterface::class);

        $this->assertSame($database2, $database1);
    }
}
