<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\CycleOrm;

use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Mockery as m;
use Spiral\Boot\MemoryInterface;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Tests\ConsoleTest;

final class UpdateCommandTest extends ConsoleTest
{
    public const ENV = [
        'SAFE_MIGRATIONS' => true,
        'USE_MIGRATIONS' => true,
        'CYCLE_SCHEMA_CACHE' => true
    ];

    public function testGetSchema(): void
    {
        $this->runCommand('cycle');

        /** @var SchemaInterface $schema */
        $schema = $this->getContainer()->get(SchemaInterface::class);

        $this->assertTrue($schema->defines('user'));

        $this->assertSame(
            \Spiral\App\Entities\User::class,
            $schema->define('user', Schema::ENTITY)
        );
    }

    public function testGetSchemaFromMemory(): void
    {
        $memory = m::mock(MemoryInterface::class);
        $this->getContainer()->bind(MemoryInterface::class, $memory);

        $memory->shouldReceive('saveData')->once();
        $memory->shouldReceive('loadData')->once()->andReturn(new Schema([]));
        $this->runCommand('cycle');

        /** @var SchemaInterface $schema */
        $schema = $this->getContainer()->get(SchemaInterface::class);

        $this->assertFalse($schema->defines('user'));
    }

    public function testSchemaDefaultsShouldBePassedToCompiler(): void
    {
        $config['schema']['defaults'][SchemaInterface::TYPECAST_HANDLER][] = 'foo';

        $memory = new class implements MemoryInterface
        {
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

        $this->runCommand('cycle');

        $this->assertSame(['foo'], $memory->loadData('cycle')['role'][SchemaInterface::TYPECAST_HANDLER]);
    }
}
