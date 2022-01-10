<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\Migrate;

use Cycle\Database\DatabaseInterface;
use Spiral\Tests\ConsoleTestCase;

final class ReplayCommandTest extends ConsoleTestCase
{
    public const ENV = [
        'SAFE_MIGRATIONS' => true,
        'USE_MIGRATIONS' => true,
    ];

    public function testMigrateReplay(): void
    {
        /** @var DatabaseInterface $db */
        $db = $this->app->get(DatabaseInterface::class);
        $this->assertSame([], $db->getTables());

        $this->runCommandDebug('migrate:init');

        $out = $this->runCommandDebug('migrate:replay');
        $this->assertStringContainsString('No', $out);

        $this->runCommandDebug('cycle:migrate');
        $this->assertCount(1, $db->getTables());

        $this->runCommandDebug('migrate');
        $this->assertCount(3, $db->getTables());

        $this->runCommandDebug('migrate:replay');
        $this->assertCount(3, $db->getTables());
    }
}
