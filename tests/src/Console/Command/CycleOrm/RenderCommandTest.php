<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\CycleOrm;

use Spiral\Console\Console;
use Spiral\Tests\ConfigAttribute;
use Spiral\Tests\ConsoleTest;
use Cycle\ORM\SchemaInterface;
use Symfony\Component\Console\Output\BufferedOutput;

final class RenderCommandTest extends ConsoleTest
{
    public function testRenderInMermaidFormat(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('cycle:render', ['--format' => 'mermaid'], [
            'classDiagram',
            'class user',
            'class role',
            'class token',
            'user --> "nullable" user : friend',
        ]);
    }

    public function testRenderInPHPFormat(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('cycle:render', ['--format' => 'php'], [
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
        $this->assertConsoleCommandOutputContainsStrings('cycle:render', ['--format' => 'color'], [
            '[35m[user][39m :: [32mdefault[39m.[32musers[39m',
            'Entity: [34mSpiral\App\Entities\User[39m',
            'Mapper: [34mcustom_mapper[39m',
        ]);
    }

    public function testRenderInInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Format `unknown` isn't supported.");

        $this->assertConsoleCommandOutputContainsStrings('cycle:render', ['--format' => 'unknown']);
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
        $this->assertConsoleCommandOutputContainsStrings('cycle:render', ['--format' => 'plain'], [
            'Mapper: custom_mapper',
            'Repository: custom_repository',
            'Scope: custom_scope',
            'Typecast: Cycle\ORM\Parser\Typecast',
            'custom_typecast_handler',
        ]);
    }

    /**
     * @return array<string, array{0:string,1:bool,2:bool,3:bool}>
     *              [format, expectAnsi, expectMermaidKeyword, expectPhp]
     */
    public static function fileFormatsProvider(): array
    {
        return [
            'plain'   => ['plain',   false, false, false],
            'color'   => ['color',   true,  false, false],
            'mermaid' => ['mermaid', false, true,  false],
            'php'     => ['php',     false, false, true],
        ];
    }

    /**
     * @dataProvider fileFormatsProvider
     */
    public function testWritesSchemaToFileForAllFormats(
        string $format,
        bool $expectAnsi,
        bool $expectMermaid,
        bool $expectPhp
    ): void {
        $out = \sys_get_temp_dir() . '/schema_' . \bin2hex(\random_bytes(4));
        @unlink($out);

        $this->assertConsoleCommandOutputContainsStrings(
            'cycle:render',
            ['--format' => $format, '--output' => $out],
            ['Schema written to']
        );

        $this->assertFileExists($out);
        $content = \file_get_contents($out);
        $this->assertIsString($content);
        $this->assertGreaterThan(0, \strlen($content));

        // ANSI detection (color)
        $hasAnsi = (bool)\preg_match('/\x1B\[[0-9;]*m/', $content);
        $this->assertSame($expectAnsi, $hasAnsi, "ANSI expectation failed for format={$format}");

        // Mermaid detection (line starts with known diagram markers)
        $hasMermaid = (bool)\preg_match('/^(graph|classDiagram|erDiagram)\b/m', $content);
        $this->assertSame($expectMermaid, $hasMermaid, "Mermaid expectation failed for format={$format}");

        // PHP detection (file starts with `<?php`)
        $hasPhp = (bool)\preg_match('/^\s*<\?php\b/m', $content);
        $this->assertSame($expectPhp, $hasPhp, "PHP expectation failed for format={$format}");

        if ($expectPhp) {
            $schema = require $out;
            $this->assertIsArray($schema);
            $this->assertNotEmpty($schema);
        } else {
            $this->assertDoesNotMatchRegularExpression('/^\s*<\?php\b/m', $content);
        }

        @\unlink($out);
    }

    public function testOverwriteOldFile(): void
    {
        $out = \sys_get_temp_dir() . '/schema_' . \bin2hex(\random_bytes(4)) . '.php';
        \file_put_contents($out, "<?php return ['_touched' => true];");

        $this->assertConsoleCommandOutputContainsStrings(
            'cycle:render',
            ['--format' => 'php', '--output' => $out],
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

        $requested = ['user', 'role'];

        $this->assertConsoleCommandOutputContainsStrings(
            'cycle:render',
            ['roles' => [implode(',', $requested)], '--format' => 'php', '--output' => $out],
            ['Schema written to']
        );

        $schema = require $out;
        $this->assertIsArray($schema);

        foreach (\array_keys($schema) as $key) {
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
                'roles'      => ['user,does_not_exist'],
                '--format'    => 'php',
                '--output'    => $out,
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

        @\unlink($out);
    }

    public function testAllUnknownRolesBehavior(): void
    {
        $out = \sys_get_temp_dir() . '/schema_' . \bin2hex(\random_bytes(4)) . '.php';
        @\unlink($out);

        $this->assertConsoleCommandOutputContainsStrings(
            'cycle:render',
            ['roles' => ['foo,bar'], '--format' => 'php', '--output' => $out],
            [
                'Nothing to write',
            ]
        );

        $this->assertFileDoesNotExist($out);

        $this->assertConsoleCommandOutputContainsStrings(
            'cycle:render',
            ['roles' => ['foo,bar'], '--format' => 'plain'],
            [
                'No roles matched the provided filter',
            ]
        );
    }

    public function testRolesFilterWorksForPlainStdout(): void
    {
        /** @var Console $console */
        $console = $this->getContainer()->get(Console::class);

        $out = new BufferedOutput();

        $code = $console->run('cycle:render', ['roles' => ['role'], '--format' => 'mermaid'], $out);

        $display = $out->fetch();

        $this->assertStringContainsString('classDiagram', $display);
        $this->assertStringContainsString('class role', $display);
        $this->assertStringNotContainsString('class user', $display);
        $this->assertStringNotContainsString('class address', $display);
    }
}
