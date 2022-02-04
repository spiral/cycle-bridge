<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\Migrate;

use Cycle\Database\Database;
use Cycle\Database\DatabaseInterface;
use Spiral\Tests\ConsoleTest;

final class InitCommandTest extends ConsoleTest
{
    public const ENV = [
        'SAFE_MIGRATIONS' => true,
        'USE_MIGRATIONS' => true,
    ];

    public function testMigrationTableShouldBeCreated(): void
    {
        /** @var Database $db */
        $db = $this->getContainer()->get(DatabaseInterface::class);

        $this->assertCount(0, $db->getTables());

        $this->runCommand('migrate:init');

        $this->assertCount(1, $db->getTables());
        $this->assertSame('migrations', $db->getTables()[0]->getName());
    }
}
