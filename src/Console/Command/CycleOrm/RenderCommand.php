<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\CycleOrm;

use Cycle\ORM\SchemaInterface;
use Cycle\Schema\Renderer\OutputSchemaRenderer;
use Cycle\Schema\Renderer\PhpSchemaRenderer;
use Cycle\Schema\Renderer\SchemaToArrayConverter;
use Spiral\Cycle\Console\Command\Migrate\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cycle\Schema\Renderer\MermaidRenderer\MermaidRenderer;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\Option;

#[AsCommand(
    name: 'cycle:render',
    description: 'Render available CycleORM schemas')
]
final class RenderCommand extends AbstractCommand
{
    #[Argument(name: 'format', description: 'The format of the output')]
    private string $format;

    #[Option(name: 'output', shortcut: 'o', description: 'Path to file for saving schema (currently only for format=php)')]
    private ?string $outputPath = null;

    #[Option(name: 'overwrite', description: 'Overwrite existing output')]
    private bool $overwrite = false;

    #[Option(name: 'role', shortcut: 'r', description: 'Specify roles for output in schema (supports comma separated values)')]
    private array $role = [];

    public function perform(
        OutputInterface $output,
        SchemaInterface $schema,
        SchemaToArrayConverter $converter,
    ): int
    {
        $renderer = match ($this->format) {
            'mermaid' => new MermaidRenderer(),
            'php' => new PhpSchemaRenderer(),
            'color' => new OutputSchemaRenderer(OutputSchemaRenderer::FORMAT_CONSOLE_COLOR),
            'plain' => new OutputSchemaRenderer(OutputSchemaRenderer::FORMAT_PLAIN_TEXT),
            default => throw new \InvalidArgumentException(
                \sprintf("Format `%s` isn't supported.", $this->format),
            ),
        };

        $schemaArray = $converter->convert($schema);
        $requestedRoles = $this->parseRolesOption($this->role);

        $existingRoles = array_keys($schemaArray);
        $rolesMap = [];
        foreach ($existingRoles as $role) {
            $rolesMap[\strtolower($role)] = true;
        }

        $resolvedRoles = [];
        $unknownRoles  = [];

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
                $output->writeln(\sprintf(
                    '<comment>No roles matched the provided filter: %s</comment>',
                    \implode(', ',  $requestedRoles)
                ));
            }
        }

        if ($unknownRoles !== [] && $schemaArray !== []) {
            $output->writeln(\sprintf(
                '<comment>Warning: unknown role(s) ignored: %s.</comment>',
                \implode(', ',  $unknownRoles)
            ));
        }

        $path = $this->outputPath;

        if ($schemaArray === []) {
            if ($path !== null) {
                $output->writeln('<comment>Nothing to write.</comment>');
            } else {
                $output->writeln('');
            }
            return self::SUCCESS;
        }

        if ($path !== null) {
            if ($this->format !== 'php') {
                $this->error('The --output option is currently supported only with format=php.');
                return self::FAILURE;
            }

            $dir = \dirname($path);

            if ($dir !== '' && $dir !== '.' && !\is_dir($dir)) {
                if (!\mkdir($dir, 0775, true) && !\is_dir($dir)) {
                    $this->error(\sprintf('Failed to create directory: %s', $dir));
                    return self::FAILURE;
                }
            }

            $exists = \is_file($path);
            if ($exists && !$this->overwrite) {
                $this->error(\sprintf('File already exists: %s (use --overwrite to replace)', $path));
                return self::FAILURE;
            }

            $rendered = $renderer->render($schemaArray);
            $payload  = \rtrim($rendered, "\r\n") . \PHP_EOL;

            $tmpDir = $dir === '.' ? \getcwd() : $dir;
            $tmp = $tmpDir !== false ? \tempnam($tmpDir, 'schema_') : false;
            if ($tmp === false) {
                $this->error('Failed to create a temporary file.');
                return self::FAILURE;
            }

            if (\file_put_contents($tmp, $payload) === false) {
                @\unlink($tmp);
                $this->error(\sprintf('Failed to write schema to temp file in "%s".', $tmpDir));
                return self::FAILURE;
            }

            if (!\rename($tmp, $path)) {
                if ($exists) {
                    @\unlink($path);
                    if (\rename($tmp, $path)) {
                        @\chmod($path, 0664);
                        $output->writeln(\sprintf('<info>Schema written to %s</info>', $path));
                        return self::SUCCESS;
                    }
                }
                @\unlink($tmp);
                $this->error(\sprintf('Failed to move temp file to "%s".', $path));
                return self::FAILURE;
            }

            @\chmod($path, 0664);
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
