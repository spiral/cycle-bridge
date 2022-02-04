<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\CycleOrm;

use Spiral\App\Entities\User;
use Spiral\Tests\ConsoleTest;
use Symfony\Component\Console\Output\BufferedOutput;

final class SyncCommandTest extends ConsoleTest
{
    public const ENV = [
        'SAFE_MIGRATIONS' => true,
        'USE_MIGRATIONS' => true,
    ];

    public function testSync(): void
    {
        $output = $this->runCommand('cycle:sync');
        $this->assertStringContainsString('default.users', $output);

        $u = new User('Antony');
        $this->getEntityManager()->persist($u)->run();

        $this->assertSame(1, $u->id);
    }

    public function testSyncDebug(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('cycle:sync', ['-vvv'], [
            'default.users',
            'create table',
            'add column',
            'add index',
        ]);

        $u = new User('Antony');
        $this->getEntityManager()->persist($u)->run();

        $this->assertSame(1, $u->id);
    }
}
