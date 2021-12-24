<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\DataGrid\GridFactoryInterface;
use Cycle\Database\DatabaseInterface;
use Spiral\DataGrid\Compiler;
use Spiral\Cycle\DataGrid\Config\GridConfig;
use Spiral\DataGrid\Grid;
use Spiral\DataGrid\GridFactory;
use Spiral\Cycle\DataGrid\GridInput;
use Spiral\DataGrid\GridInterface;
use Spiral\DataGrid\InputInterface;
use Spiral\Cycle\DataGrid\Response\GridResponse;
use Spiral\Cycle\DataGrid\Response\GridResponseInterface;
use Spiral\Cycle\DataGrid\Writer\BetweenWriter;
use Spiral\Cycle\DataGrid\Writer\QueryWriter;

final class DataGridBootloader extends Bootloader
{
    protected const SINGLETONS = [
        InputInterface::class        => GridInput::class,
        GridInterface::class         => Grid::class,
        GridFactoryInterface::class  => GridFactory::class,
        GridFactory::class           => GridFactory::class, // Deprecated behaviour
        Compiler::class              => [self::class, 'compiler'],
        GridResponseInterface::class => GridResponse::class,
    ];

    /** @var ConfiguratorInterface */
    private $config;

    /**
     * @param ConfiguratorInterface $config
     */
    public function __construct(ConfiguratorInterface $config)
    {
        $this->config = $config;
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

    /**
     * @param ContainerInterface $container
     * @param Compiler           $compiler
     * @param GridConfig         $config
     * @return Compiler
     */
    public function compiler(ContainerInterface $container, Compiler $compiler, GridConfig $config): Compiler
    {
        if ($container->has(DatabaseInterface::class)) {
            foreach ($config->getWriters() as $writer) {
                $compiler->addWriter($container->get($writer));
            }
        }

        return $compiler;
    }
}
