<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\CycleOrm;

use Spiral\Tests\ConfigAttribute;
use Spiral\Tests\ConsoleTest;
use Cycle\ORM\SchemaInterface;

final class RenderCommandTest extends ConsoleTest
{
    public function testRenderSchema(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('cycle:render', ['--no-color' => true], [
            '[user] :: default.users',
            'Entity: Spiral\App\Entities\User',
            'Mapper: Cycle\ORM\Mapper\Mapper',
            'Repository: Cycle\ORM\Select\Repository',
        ]);
    }

    public function testRenderMermaid(): void
    {
        $this->assertConsoleCommandOutputContainsStrings(
            'cycle:render',
            ['--no-color' => true, '--renderer' => 'mermaid'],
            [
                'classDiagram',
                'class user',
                'class role',
                'class token',
                'user --> "nullable" user : friend',
            ]
        );
    }

    public function testRenderPHP(): void
    {
        $this->assertConsoleCommandOutputContainsStrings(
            'cycle:render',
            ['--no-color' => true, '--renderer' => 'php'],
            [
                '<?php',
                'declare(strict_types=1);',
                'use Cycle\ORM\Relation;',
                'use Cycle\ORM\SchemaInterface as Schema;',
                'return [',
            ]
        );
    }

    public function testNullRendererOption(): void
    {
        $this->assertConsoleCommandOutputContainsStrings(
            'cycle:render', ['--no-color' => true, '--renderer' => null],
            [
                '[user] :: default.users',
                'Entity: Spiral\App\Entities\User',
                'Mapper: Cycle\ORM\Mapper\Mapper',
                'Repository: Cycle\ORM\Select\Repository',
            ]
        );
    }

    #[ConfigAttribute(path: 'cycle.schema.defaults', value: [
        SchemaInterface::MAPPER => 'custom_mapper',
        SchemaInterface::REPOSITORY => 'custom_repository',
        SchemaInterface::SCOPE => 'custom_scope',
        SchemaInterface::TYPECAST_HANDLER => [
            \Cycle\ORM\Parser\Typecast::class,
            'custom_typecast_handler',
        ],
    ])]
    public function testRedefineSchemaDefaults()
    {
        $this->assertConsoleCommandOutputContainsStrings('cycle:render', ['--no-color' => true], [
            'Mapper: custom_mapper',
            'Repository: custom_repository',
            'Scope: custom_scope',
            'Typecast: Cycle\ORM\Parser\Typecast',
            'custom_typecast_handler',
        ]);
    }
}
