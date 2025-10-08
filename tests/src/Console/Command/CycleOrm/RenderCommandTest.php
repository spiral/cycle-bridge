<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\CycleOrm;

use Spiral\Console\Console;
use Spiral\Tests\ConfigAttribute;
use Spiral\Tests\ConsoleTest;
use Cycle\ORM\SchemaInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\BufferedOutput;

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

    #[ConfigAttribute(path: 'cycle.schema.defaults', value: [
        SchemaInterface::MAPPER => 'custom_mapper',
        SchemaInterface::REPOSITORY => 'custom_repository',
        SchemaInterface::SCOPE => 'custom_scope',
        SchemaInterface::TYPECAST_HANDLER => [
            \Cycle\ORM\Parser\Typecast::class,
            'custom_typecast_handler',
        ],
    ])]
    public function testRenderInColorFormat(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('cycle:render', ['format' => 'color'], [
            '[35m[user][39m :: [32mdefault[39m.[32musers[39m',
            'Entity: [34mSpiral\App\Entities\User[39m',
            'Mapper: [34mcustom_mapper[39m',
        ]);
    }

    public function testRenderInInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Format `unknown` isn't supported.");

        $this->assertConsoleCommandOutputContainsStrings('cycle:render', ['format' => 'unknown']);
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
    public function testRedefineSchemaDefaults(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('cycle:render', ['format' => 'plain'], [
            'Mapper: custom_mapper',
            'Repository: custom_repository',
            'Scope: custom_scope',
            'Typecast: Cycle\ORM\Parser\Typecast',
            'custom_typecast_handler',
        ]);
    }

    public function testOutputOnlyForPhpFormat(): void
    {
        $out = \sys_get_temp_dir() . '/schema_' . bin2hex(\random_bytes(4)) . '.php';
        @unlink($out);

        $this->assertConsoleCommandOutputContainsStrings(
            'cycle:render',
            ['format' => 'mermaid', '--output' => $out],
            ['The --output option is currently supported only with format=php.']
        );

        $this->assertFileDoesNotExist($out);
    }

    public function testWritesPhpSchemaToFile(): void
    {
        $out = \sys_get_temp_dir() . '/schema_' . bin2hex(\random_bytes(4)) . '.php';
        @unlink($out);

        $this->assertConsoleCommandOutputContainsStrings(
            'cycle:render',
            ['format' => 'php', '--output' => $out, '--overwrite' => true],
            ['Schema written to']
        );

        $this->assertFileExists($out);
        $schema = require $out;
        $this->assertIsArray($schema);

        @unlink($out);
    }

    public function testPreventOverwriteWithoutFlag(): void
    {
        $out = \sys_get_temp_dir() . '/schema_' . \bin2hex(\random_bytes(4)) . '.php';
        \file_put_contents($out, "<?php return ['_touched' => true];");

        $this->assertConsoleCommandOutputContainsStrings(
            'cycle:render',
            ['format' => 'php', '--output' => $out],
            ['File already exists:', 'use --overwrite to replace']
        );

        // файл остался прежним
        $schema = require $out;
        $this->assertArrayHasKey('_touched', $schema);

        @unlink($out);
    }

    public function testOverwriteWithFlag(): void
    {
        $out = \sys_get_temp_dir() . '/schema_' . \bin2hex(\random_bytes(4)) . '.php';
        \file_put_contents($out, "<?php return ['_touched' => true];");

        $this->assertConsoleCommandOutputContainsStrings(
            'cycle:render',
            ['format' => 'php', '--output' => $out, '--overwrite' => true],
            ['Schema written to']
        );

        $schema = require $out;
        $this->assertIsArray($schema);
        $this->assertArrayNotHasKey('_touched', $schema);

        @unlink($out);
    }

    public function testRolesFilterSubset(): void
    {
        $out = \sys_get_temp_dir() . '/schema_' . \bin2hex(\random_bytes(4)) . '.php';
        @unlink($out);

        // В фикстурах Spiral обычно есть роли 'user','role','token' (смотри существующий тест mermaid)
        $requested = ['user', 'role'];

        $this->assertConsoleCommandOutputContainsStrings(
            'cycle:render',
            ['format' => 'php', '--output' => $out, '--overwrite' => true, '--role' => [implode(',', $requested)]],
            ['Schema written to']
        );

        $schema = require $out;
        $this->assertIsArray($schema);

        // Все ключи схемы должны быть из запрошенного набора
        foreach (array_keys($schema) as $key) {
            $this->assertContains($key, $requested);
        }

        @unlink($out);
    }

    public function testWarnOnUnknownRolesButProceed(): void
    {
        $out = \sys_get_temp_dir() . '/schema_' . \bin2hex(\random_bytes(4)) . '.php';
        @unlink($out);

        // 'user' существует, 'does_not_exist' — нет
        $this->assertConsoleCommandOutputContainsStrings(
            'cycle:render',
            [
                'format'    => 'php',
                '--output'    => $out,
                '--overwrite' => true,
                '--role'      => ['user,does_not_exist'],
            ],
            [
                'Warning: unknown role(s) ignored: does_not_exist.',
                'Schema written to',
            ]
        );

        $schema = require $out;
        $this->assertIsArray($schema);
        $this->assertArrayHasKey('user', $schema);
        $this->assertArrayNotHasKey('does_not_exist', $schema);

        @unlink($out);
    }

    public function testAllUnknownRolesLeadsToNothingToWrite(): void
    {
        $out = \sys_get_temp_dir() . '/schema_' . \bin2hex(\random_bytes(4)) . '.php';
        @unlink($out);

        $this->assertConsoleCommandOutputContainsStrings(
            'cycle:render',
            ['format' => 'php', '--output' => $out, '--role' => ['foo,bar']],
            [
                'Nothing to write.',
            ]
        );

        $this->assertFileDoesNotExist($out);
    }

    public function testRolesCsvAndRepeatOptionsAreMerged(): void
    {
        $out = sys_get_temp_dir() . '/schema_' . bin2hex(random_bytes(4)) . '.php';
        @unlink($out);

        // проверяем смешанный ввод: повторяемая опция + CSV + разный регистр
        $this->assertConsoleCommandOutputContainsStrings(
            'cycle:render',
            [
                'format'    => 'php',
                '--output'    => $out,
                '--overwrite' => true,
                '--role'      => ['User', 'role,address'],
            ],
            ['Schema written to']
        );

        $schema = require $out;
        $this->assertIsArray($schema);

        // ожидаем хотя бы user/role/token (если они есть в фикстуре)
        $keys = array_keys($schema);
        $this->assertNotEmpty($keys);
        foreach ($keys as $k) {
            $this->assertContains($k, ['user', 'role', 'address']);
        }

        @unlink($out);
    }

    public function testRolesFilterWorksForPlainStdout(): void
    {
        /** @var Console $console */
        $console = $this->getContainer()->get(Console::class);

        $out = new BufferedOutput();

        $code = $console->run('cycle:render', ['format' => 'mermaid', '--role' => ['role']], $out);

        $display = $out->fetch();

        $this->assertStringContainsString('classDiagram', $display);
        $this->assertStringContainsString('class role', $display);
        $this->assertStringNotContainsString('class user', $display);
        $this->assertStringNotContainsString('class address', $display);
    }
}
