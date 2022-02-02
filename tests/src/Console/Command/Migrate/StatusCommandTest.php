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

    public function testCheckMigratinoStatus(): void
    {
        /** @var Database $db */
        $db = $this->app->get(DatabaseInterface::class);
        $this->assertCount(0, $db->getTables());

        $out = $this->runCommandDebug('migrate:status');
        $this->assertStringContainsString('No migrations', $out);

        $this->runCommandDebug('migrate:init');

        $out = $this->runCommandDebug('migrate:status');
        $this->assertStringContainsString('No migrations', $out);

        $this->runCommandDebug('cycle:migrate');
        $this->assertCount(1, $db->getTables());

        $out = $this->runCommandDebug('migrate:status');
        $this->assertStringContainsString('not executed yet', $out);

        $this->runCommandDebug('migrate');
        $this->assertCount(3, $db->getTables());

        $out2 = $this->runCommandDebug('migrate:status');
        $this->assertNotSame($out, $out2);
    }
}
