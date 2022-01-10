<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\CycleOrm;

use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Mockery as m;
use Spiral\Boot\MemoryInterface;
use Spiral\Tests\ConsoleTestCase;

final class UpdateCommandTest extends ConsoleTestCase
{
    public const ENV = [
        'SAFE_MIGRATIONS' => true,
        'USE_MIGRATIONS' => true
    ];

    public function testGetSchema(): void
    {
        $this->app->console()->run('cycle');

        /** @var SchemaInterface $schema */
        $schema = $this->app->get(SchemaInterface::class);

        $this->assertTrue($schema->defines('user'));

        $this->assertSame(
            \Spiral\App\Entities\User::class,
            $schema->define('user', Schema::ENTITY)
        );
    }

    public function testGetSchemaFromMemory(): void
    {
        $memory = m::mock(MemoryInterface::class);
        $this->app->getContainer()->bind(MemoryInterface::class, $memory);

        $memory->shouldReceive('saveData');
        $memory->shouldReceive('loadData')->once()->andReturn(new Schema([]));
        $this->runCommandDebug('cycle');

        /** @var SchemaInterface $schema */
        $schema = $this->app->get(SchemaInterface::class);

        $this->assertFalse($schema->defines('user'));
    }
}
