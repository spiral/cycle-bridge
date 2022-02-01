<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\CycleOrm;

use Spiral\Tests\ConsoleTest;
use Cycle\ORM\SchemaInterface;

final class RenderCommandTest extends ConsoleTest
{
    public function testRenderSchema()
    {
        $output = $this->runCommandDebug('cycle:render', ['--no-color' => true]);

        $userOutput = [
            '[user] :: default.users',
            'Entity: Spiral\App\Entities\User',
            'Mapper: Cycle\ORM\Mapper\Mapper',
            'Repository: Cycle\ORM\Select\Repository'
        ];

        foreach ($userOutput as $line) {
            $this->assertStringContainsString($line, $output);
        }
    }

    public function testRedefineSchemaDefaults()
    {
        $this->updateConfig('cycle.schema.defaults', [
            SchemaInterface::MAPPER => 'custom_mapper',
            SchemaInterface::REPOSITORY => 'custom_repository',
            SchemaInterface::SCOPE => 'custom_scope',
            SchemaInterface::TYPECAST_HANDLER => [
                \Cycle\ORM\Parser\Typecast::class,
                'custom_typecast_handler',
            ],
        ]);

        $output = $this->runCommandDebug('cycle:render', ['--no-color' => true]);

        $userOutput = [
            'Mapper: custom_mapper',
            'Repository: custom_repository',
            'Scope: custom_scope',
            'Typecast: Cycle\ORM\Parser\Typecast',
            'custom_typecast_handler'
        ];

        foreach ($userOutput as $line) {
            $this->assertStringContainsString($line, $output);
        }
    }
}
