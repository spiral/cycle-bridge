<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\CycleOrm;

use Cycle\ORM\SchemaInterface;
use Spiral\App\Entities\User;
use Spiral\Boot\MemoryInterface;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Testing\Attribute\Env;
use Spiral\Tests\ConsoleTest;

final class SyncCommandTest extends ConsoleTest
{
    public const ENV = [
        'USE_MIGRATIONS' => true,
    ];

    #[Env('SAFE_MIGRATIONS', 'false')]
    public function testUnsafeSync(): void
    {
        $output = $this->runCommand('cycle:sync', [
            '--no-interaction' => true,
        ]);

        $this->assertStringContainsString('This operation is not recommended for production environment.', $output);
        $this->assertStringContainsString('Cancelling operation...', $output);
    }

    #[Env('SAFE_MIGRATIONS', 'true')]
    public function testSync(): void
    {
        $output = $this->runCommand('cycle:sync');
        $this->assertStringContainsString('default.users', $output);

        $u = new User('Antony');
        $this->getEntityManager()->persist($u)->run();

        $this->assertSame(1, $u->id);
    }

    #[Env('SAFE_MIGRATIONS', 'true')]
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

    #[Env('SAFE_MIGRATIONS', 'true')]
    public function testSchemaDefaultsShouldBePassedToCompiler(): void
    {
        $config['schema']['defaults'][SchemaInterface::TYPECAST_HANDLER][] = 'foo';

        $memory = new class implements MemoryInterface {
            private mixed $data;

            public function loadData(string $section): mixed
            {
                return $this->data[$section];
            }

            public function saveData(string $section, mixed $data): void
            {
                $this->data[$section] = $data;
            }
        };

        $this->getContainer()->bind(CycleConfig::class, new CycleConfig($config));
        $this->getContainer()->bindSingleton(MemoryInterface::class, $memory);

        $this->runCommand('cycle:sync');

        $this->assertSame(['foo'], $memory->loadData('cycle')['role'][SchemaInterface::TYPECAST_HANDLER]);
    }
}
