<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\CycleOrm;

use Spiral\Tests\ConfigAttribute;
use Spiral\Tests\ConsoleTest;
use Cycle\ORM\SchemaInterface;

final class RenderCommandTest extends ConsoleTest
{
    public function testRenderInMermaidFormat(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('cycle:render', ['format' => 'mermaid'], [
            'classDiagram',
            'class user',
            'class role',
            'class token',
            'user --> "nullable" user : friend',
        ]);
    }

    public function testRenderInPHPFormat(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('cycle:render', ['format' => 'php'], [
            '<?php',
            'declare(strict_types=1);',
            'use Cycle\ORM\Relation;',
            'use Cycle\ORM\SchemaInterface as Schema;',
            'return [',
        ]);
    }

    public function testRenderInInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Format \'123\' doesn\'t exists.');

        $this->assertConsoleCommandOutputContainsStrings('cycle:render', ['format' => '123']);
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
        $this->assertConsoleCommandOutputContainsStrings('cycle:render', ['format' => 'plain'], [
            'Mapper: custom_mapper',
            'Repository: custom_repository',
            'Scope: custom_scope',
            'Typecast: Cycle\ORM\Parser\Typecast',
            'custom_typecast_handler',
        ]);
    }
}
