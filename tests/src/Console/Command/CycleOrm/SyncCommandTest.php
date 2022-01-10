<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\CycleOrm;

use Spiral\App\Entities\User;
use Spiral\Tests\ConsoleTestCase;
use Symfony\Component\Console\Output\BufferedOutput;

final class SyncCommandTest extends ConsoleTestCase
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
        $output = $this->runCommand(
            'cycle:sync',
            verbosityLevel: BufferedOutput::VERBOSITY_VERY_VERBOSE
        );

        $this->assertStringContainsString('default.users', $output);
        $this->assertStringContainsString('create table', $output);
        $this->assertStringContainsString('add column', $output);
        $this->assertStringContainsString('add index', $output);

        $u = new User('Antony');
        $this->getEntityManager()->persist($u)->run();

        $this->assertSame(1, $u->id);
    }
}
