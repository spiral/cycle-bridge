<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\CycleOrm;

use Cycle\ORM\SchemaInterface;
use Cycle\Schema\Renderer\OutputSchemaRenderer;
use Cycle\Schema\Renderer\PhpSchemaRenderer;
use Cycle\Schema\Renderer\SchemaToArrayConverter;
use Spiral\Cycle\Console\Command\Migrate\AbstractCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Cycle\Schema\Renderer\MermaidRenderer\MermaidRenderer;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\Option;

/**
 * Renders Cycle ORM schema in various formats.
 *
 * Exports the complete Cycle ORM schema or specific entity roles to different
 * output formats including PHP arrays, Mermaid diagrams, colored console output,
 * or plain text. Output can be displayed in terminal or saved to a file.
 *
 * ```bash
 * # Display full schema with colors (default for ANSI terminals)
 * php app.php cycle:render
 *
 * # Display schema for specific entity roles only
 * php app.php cycle:render user,post,comment
 *
 * # Export entire schema as PHP array to file
 * php app.php cycle:render --format=php --output=cycle-schema.php
 *
 * # Export filtered schema to PHP file (short option)
 * php app.php cycle:render user,post --format=php -o schema.php
 *
 * # Generate Mermaid ER diagram
 * php app.php cycle:render --format=mermaid --output=schema.mmd
 *
 * # Export plain text schema without colors
 * php app.php cycle:render --format=plain
 *
 * # Export specific roles as Mermaid diagram
 * php app.php cycle:render user,post,comment --format=mermaid -o entities.mmd
 *
 * # Export to file with automatic directory creation
 * php app.php cycle:render --format=php -o export/schemas/cycle-schema.php
 * ```
 */
#[AsCommand(
    name: 'cycle:render',
    description: 'Render Cycle ORM schema in various formats (php, mermaid, color, plain).',
)]
final class RenderCommand extends AbstractCommand
{
    #[Argument(name: 'roles', description: 'Comma-separated roles to export (e.g. "user,post,comment"); omit to export full schema.')]
    private array $roles = [];

    #[Option(name: 'output', shortcut: 'o', description: 'Write/overwrite output to file (path). If omitted, prints to STDOUT.')]
    private ?string $outputPath = null;

    #[Option(name: 'format', description: "Output format: php|mermaid|color|plain (default: 'color' for ANSI-capable terminals, otherwise 'plain').")]
    private ?string $format = null;

    public function perform(
        OutputInterface $output,
        SchemaInterface $schema,
        SchemaToArrayConverter $converter,
    ): int {
        $renderer = match ($this->format) {
            'mermaid' => new MermaidRenderer(),
            'php' => new PhpSchemaRenderer(),
            'color' => new OutputSchemaRenderer(OutputSchemaRenderer::FORMAT_CONSOLE_COLOR),
            'plain' => new OutputSchemaRenderer(OutputSchemaRenderer::FORMAT_PLAIN_TEXT),
            null => new OutputSchemaRenderer(
                $output->isDecorated() && $this->outputPath === null ?
                    OutputSchemaRenderer::FORMAT_CONSOLE_COLOR : OutputSchemaRenderer::FORMAT_PLAIN_TEXT,
            ),
            default => throw new \InvalidArgumentException(
                \sprintf("Format `%s` isn't supported.", $this->format),
            ),
        };

        $schemaArray = $converter->convert($schema);

        $requestedRoles = $this->parseRolesOption($this->roles);

        if ($requestedRoles !== []) {
            $lowerMap = [];
            $resolvedRoles = [];
            $unknownRoles = [];

            foreach ($schemaArray as $role => $v) {
                $lowerMap[\strtolower($role)] = $v;
            }

            foreach ($requestedRoles as $role) {
                $s = $schemaArray[$role] ?? $lowerMap[\strtolower($role)] ?? null;
                $s === null
                    ? $unknownRoles[] = $role
                    : $resolvedRoles[$role] = $s;
            }

            $unknownRoles !== [] and $this->warning(\sprintf(
                'No roles were found for `%s`.',
                \implode('`, `', $unknownRoles),
            ));
            $schemaArray = $resolvedRoles;
            unset($resolvedRoles, $lowerMap, $unknownRoles);
        }

        if ($schemaArray === []) {
            return self::FAILURE;
        }

        $path = $this->outputPath;
        $rendered = $renderer->render($schemaArray);

        if ($path === null) {
            $output->writeln($rendered);
            return self::SUCCESS;
        }

        $dir = \dirname($path);
        if ($dir !== '' && $dir !== '.' && !\is_dir($dir)) {
            if (!\mkdir($dir, 0775, true) && !\is_dir($dir)) {
                $this->error(\sprintf('Failed to create directory: %s.', $dir));
                return self::FAILURE;
            }
        }


        if (\file_put_contents($path, $rendered) === false) {
            $this->error(\sprintf('Failed to write schema to file: %s.', $path));
            return self::FAILURE;
        }

        $this->info(\sprintf('Schema successfully written to %s.', $path));
        return self::SUCCESS;
    }

    /**
     * @return array<string>
     */
    private function parseRolesOption(array $raw): array
    {
        $allRoles = [];
        foreach ($raw as $piece) {
            $roles = \array_map('trim', \explode(',', $piece));
            foreach ($roles as $role) {
                if ($role !== '') {
                    $allRoles[] = $role;
                }
            }
        }
        return \array_values(\array_unique($allRoles));
    }
}
