<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\CycleOrm;

use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Mockery as m;
use Spiral\Boot\MemoryInterface;
use Spiral\Tests\ConsoleTest;

final class UpdateCommandTest extends ConsoleTest
{
    public const ENV = [
        'SAFE_MIGRATIONS' => true,
        'USE_MIGRATIONS' => true
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

        $memory->shouldReceive('saveData');
        $memory->shouldReceive('loadData')->once()->andReturn(new Schema([]));
        $this->runCommand('cycle');

        /** @var SchemaInterface $schema */
        $schema = $this->getContainer()->get(SchemaInterface::class);

        $this->assertFalse($schema->defines('user'));
    }
}
