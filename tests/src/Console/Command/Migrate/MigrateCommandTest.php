<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\Migrate;

use Cycle\Database\DatabaseInterface;
use Spiral\Testing\Attribute\Env;
use Spiral\Tests\ConsoleTest;

final class MigrateCommandTest extends ConsoleTest
{
    public const ENV = [
        'USE_MIGRATIONS' => true,
    ];

    #[Env('SAFE_MIGRATIONS', true)]
    public function testMigrate(): void
    {
        $db = $this->initMigrations();
        $this->runCommand('migrate');
        $this->assertCount(4, $db->getTables());
    }

    #[Env('SAFE_MIGRATIONS', false)]
    public function tesForceMigrate(): void
    {
        $db = $this->initMigrations();
        $this->runCommand('migrate', ['--force' => true]);
        $this->assertCount(4, $db->getTables());
    }

    #[Env('SAFE_MIGRATIONS', false)]
    public function testUnsafeMigrate(): void
    {
        $db = $this->initMigrations();
        $output = $this->runCommand('migrate');
        $this->assertStringContainsString('Confirmation is required to run migrations!', $output);
        $this->assertStringContainsString('Cancelling operation...', $output);
        $this->assertCount(1, $db->getTables());
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function initMigrations(): DatabaseInterface
    {
        /** @var DatabaseInterface $db */
        $db = $this->getContainer()->get(DatabaseInterface::class);
        $this->assertSame([], $db->getTables());

        $this->runCommand('migrate:init');
        $this->runCommand('cycle:migrate');

        $this->assertCount(1, $db->getTables());

        return $db;
    }
}
