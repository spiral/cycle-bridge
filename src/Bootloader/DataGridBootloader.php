<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Cycle\Database\DatabaseManager;
use Cycle\Database\DatabaseProviderInterface;
use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\AttributesBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Cycle\DataGrid\Config\GridConfig;
use Spiral\Cycle\DataGrid\GridInput;
use Spiral\Cycle\DataGrid\Response\GridResponse;
use Spiral\Cycle\DataGrid\Response\GridResponseInterface;
use Spiral\Cycle\DataGrid\Writer\BetweenWriter;
use Spiral\Cycle\DataGrid\Writer\QueryWriter;
use Spiral\DataGrid\Compiler;
use Spiral\DataGrid\Grid;
use Spiral\DataGrid\GridFactory;
use Spiral\DataGrid\GridFactoryInterface;
use Spiral\DataGrid\GridInterface;
use Spiral\DataGrid\InputInterface;

final class DataGridBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        AttributesBootloader::class
    ];

    protected const SINGLETONS = [
        InputInterface::class => GridInput::class,
        GridInterface::class => Grid::class,
        GridFactoryInterface::class => GridFactory::class,
        GridFactory::class => GridFactory::class, // Deprecated behavior
        Compiler::class => [self::class, 'compiler'],
        GridResponseInterface::class => GridResponse::class,
    ];


    public function __construct(
        private ConfiguratorInterface $config
    ) {
    }

    /**
     * Inits default config.
     */
    public function boot(): void
    {
        $this->config->setDefaults(GridConfig::CONFIG, [
            'writers' => [QueryWriter::class, BetweenWriter::class],
        ]);
    }

    public function compiler(ContainerInterface $container, Compiler $compiler, GridConfig $config): Compiler
    {
        if ($container->has(DatabaseProviderInterface::class)) {
            foreach ($config->getWriters() as $writer) {
                $compiler->addWriter($container->get($writer));
            }
        }

        return $compiler;
    }
}
