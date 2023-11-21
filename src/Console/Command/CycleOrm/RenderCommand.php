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

final class RenderCommand extends AbstractCommand
{
    protected const SIGNATURE = 'cycle:render {format=color : Output format}';
    protected const DESCRIPTION = 'Render available CycleORM schemas';

    public function perform(
        OutputInterface $output,
        SchemaInterface $schema,
        SchemaToArrayConverter $converter
    ): int {
        $renderer = match ($this->argument('format')) {
            'mermaid' => new MermaidRenderer(),
            'php' => new PhpSchemaRenderer(),
            'color' => new OutputSchemaRenderer(OutputSchemaRenderer::FORMAT_CONSOLE_COLOR),
            'plain' => new OutputSchemaRenderer(OutputSchemaRenderer::FORMAT_PLAIN_TEXT),
            default => throw new \InvalidArgumentException(
                sprintf("Format `%s` isn't supported.", $this->argument('format'))
            )
        };

        $output->writeln(
            $renderer->render(
                $converter->convert($schema)
            )
        );

        return self::SUCCESS;
    }
}
