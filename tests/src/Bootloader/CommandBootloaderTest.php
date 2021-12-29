<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Spiral\Cycle\Console\Command\CycleOrm;
use Spiral\Cycle\Console\Command\Database;
use Spiral\Cycle\Console\Command\Migrate;
use Spiral\Tests\TestCase;

final class CommandBootloaderTest extends TestCase
{
    public function testCommandsShouldBeRegistered()
    {
        $commands = [
            Database\ListCommand::class,
            Database\TableCommand::class,
            CycleOrm\UpdateCommand::class,
            CycleOrm\RenderCommand::class,
            CycleOrm\SyncCommand::class,
            CycleOrm\MigrateCommand::class,
            Migrate\InitCommand::class,
            Migrate\StatusCommand::class,
            Migrate\MigrateCommand::class,
            Migrate\RollbackCommand::class,
            Migrate\ReplayCommand::class,
        ];

        $registeredCommands = $this->getConfig('console')['commands'] ?? [];

        foreach ($commands as $command) {
            $this->assertContains($command, $registeredCommands);
        }
    }
}
