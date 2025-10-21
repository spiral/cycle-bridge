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

#[AsCommand(
    name: 'cycle:render',
    description: "Render Cycle ORM schema.\n\n"
    . "Examples:\n"
    . "  php app.php cycle:render user,post,comment\n"
    . "  php app.php cycle:render --format=php --output=cycle-schema.php\n\n"
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
    ): int
    {
        if ($this->format === null) {
            $renderer = new OutputSchemaRenderer(
                $output->isDecorated() && $this->outputPath === null ?
                    OutputSchemaRenderer::FORMAT_CONSOLE_COLOR : OutputSchemaRenderer::FORMAT_PLAIN_TEXT
            );
        } else {
            $renderer = match ($this->format) {
                'mermaid' => new MermaidRenderer(),
                'php' => new PhpSchemaRenderer(),
                'color' => new OutputSchemaRenderer(OutputSchemaRenderer::FORMAT_CONSOLE_COLOR),
                'plain' => new OutputSchemaRenderer(OutputSchemaRenderer::FORMAT_PLAIN_TEXT),
                default => throw new \InvalidArgumentException(
                    \sprintf("Format `%s` isn't supported.", $this->format),
                ),
            };
        }

        $schemaArray = $converter->convert($schema);

        $requestedRoles = $this->parseRolesOption($this->roles);
        $existingRoles = array_keys($schemaArray);
        $rolesMap = [];
        foreach ($existingRoles as $role) {
            $rolesMap[\strtolower($role)] = true;
        }

        $resolvedRoles = [];
        $unknownRoles = [];

        foreach ($requestedRoles as $role) {
            $key = \strtolower($role);
            if (isset($rolesMap[$key])) {
                $resolvedRoles[$key] = true;
            } else {
                $unknownRoles[] = $role;
            }
        }

        if ($requestedRoles !== []) {
            $schemaArray = \array_intersect_key($schemaArray, $resolvedRoles);
            if ($schemaArray === []) {
                $output->writeln(\sprintf( '<comment>No roles matched the provided filter: %s</comment>', \implode(', ', $requestedRoles) ));
            }
        }

        if ($unknownRoles !== [] && $schemaArray !== []) {
            $output->writeln(\sprintf('<comment>Warning: unknown role(s) ignored: %s.</comment>', \implode(', ', $unknownRoles)));
        }

        $path = $this->outputPath;
        if ($path !== null) {
            $dir = \dirname($path);
            if ($dir !== '' && $dir !== '.' && !\is_dir($dir)) {
                if (!\mkdir($dir, 0775, true) && !\is_dir($dir)) {
                    $this->error(\sprintf('Failed to create directory: %s', $dir));
                    return self::FAILURE;
                }
            }

            $rendered = $renderer->render($schemaArray);
            $payload  = \rtrim($rendered, "\r\n") . \PHP_EOL;

            if ($schemaArray !== []) {
                if (\file_put_contents($path, $payload) === false) {
                    $this->error(\sprintf('Failed to write schema to temp file in "%s".', $path));
                    return self::FAILURE;
                }
            } else {
                $this->error(\sprintf('Nothing to write to "%s".', $path));
                return self::FAILURE;
            }

            $output->writeln(\sprintf('<info>Schema written to %s</info>', $path));
            return self::SUCCESS;
        }

        $rendered = $renderer->render($schemaArray);
        $output->writeln($rendered);

        return self::SUCCESS;
    }


    /**
     * @param array $raw
     * @return array<string>
     */
    private function parseRolesOption(array $raw): array
    {
        $allRoles = [];
        foreach ($raw as $piece) {
            $roles = \array_map('trim', explode(',', $piece));
            foreach ($roles as $role) {
                if ($role !== '') {
                    $allRoles[] = $role;
                }
            }
        }
        return \array_values(\array_unique($allRoles));
    }
}
