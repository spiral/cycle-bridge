<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\Migrate;

use Cycle\Database\Database;
use Cycle\Database\DatabaseInterface;
use Spiral\Tests\ConsoleTest;

final class StatusCommandTest extends ConsoleTest
{
    public const ENV = [
        'SAFE_MIGRATIONS' => true,
        'USE_MIGRATIONS' => true,
    ];

    public function testCheckMigrationStatus(): void
    {
        /** @var Database $db */
        $db = $this->getContainer()->get(DatabaseInterface::class);
        $this->assertCount(0, $db->getTables());

        $this->assertConsoleCommandOutputContainsStrings('migrate:status', [], 'No migrations');

        $this->runCommand('migrate:init');

        $out = $this->runCommand('migrate:status');
        $this->assertStringContainsString('No migrations', $out);

        $this->runCommand('cycle:migrate');
        $this->assertCount(1, $db->getTables());

        $out = $this->runCommand('migrate:status');
        $this->assertStringContainsString('not executed yet', $out);

        $this->runCommand('migrate');
        $this->assertCount(4, $db->getTables());

        $out2 = $this->runCommand('migrate:status');
        $this->assertNotSame($out, $out2);
    }
}
