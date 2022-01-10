<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\CycleOrm;

use Cycle\ORM\SchemaInterface;
use Cycle\Schema\Renderer\OutputSchemaRenderer;
use Cycle\Schema\Renderer\SchemaToArrayConverter;
use Spiral\Cycle\Console\Command\Migrate\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class RenderCommand extends AbstractCommand
{
    protected const NAME = 'cycle:render';
    protected const DESCRIPTION = 'Render available CycleORM schemas';
    protected const OPTIONS = [
        ['no-color', 'nc', InputOption::VALUE_NONE, 'Display output without colors.'],
    ];

    public function perform(
        OutputInterface $output,
        SchemaInterface $schema,
        SchemaToArrayConverter $converter
    ): int {
        $format = $this->option('no-color')  ?
            OutputSchemaRenderer::FORMAT_PLAIN_TEXT :
            OutputSchemaRenderer::FORMAT_CONSOLE_COLOR;

        $renderer = new OutputSchemaRenderer($format);

        $output->writeln(
            $renderer->render(
                $converter->convert($schema)
            )
        );

        return self::SUCCESS;
    }
}
