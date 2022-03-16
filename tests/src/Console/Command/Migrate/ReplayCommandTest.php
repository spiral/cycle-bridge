<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\Migrate;

use Cycle\Database\DatabaseInterface;
use Spiral\Tests\ConsoleTest;

final class ReplayCommandTest extends ConsoleTest
{
    public const ENV = [
        'SAFE_MIGRATIONS' => true,
        'USE_MIGRATIONS' => true,
    ];

    public function testMigrateReplay(): void
    {
        /** @var DatabaseInterface $db */
        $db = $this->getContainer()->get(DatabaseInterface::class);
        $this->assertSame([], $db->getTables());

        $this->runCommand('migrate:init');

        $this->assertConsoleCommandOutputContainsStrings('migrate:replay', [], 'No');

        $this->runCommand('cycle:migrate');
        $this->assertCount(1, $db->getTables());

        $this->runCommand('migrate');
        $this->assertCount(4, $db->getTables());

        $this->runCommand('migrate:replay');
        $this->assertCount(4, $db->getTables());
    }
}
