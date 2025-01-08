<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\CycleOrm;

use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
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

    public function testSchemaDefaultsShouldBePassedToCompiler(): void
    {
        $config = $this->getContainer()->get(CycleConfig::class)->toArray();
        $config['schema']['defaults'][SchemaInterface::TYPECAST_HANDLER][] = 'foo';

        $this->getContainer()->bind(CycleConfig::class, new CycleConfig($config));

        $this->runCommand('cycle');

        $this->assertSame(
            ['foo'],
            $this->getContainer()->get(SchemaInterface::class)->define('role', SchemaInterface::TYPECAST_HANDLER)
        );
    }
}
