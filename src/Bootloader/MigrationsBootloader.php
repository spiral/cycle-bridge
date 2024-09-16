<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\FileRepository;
use Cycle\Migrations\Migrator;
use Cycle\Migrations\RepositoryInterface;
use Cycle\Schema\Generator\Migrations\NameBasedOnChangesGenerator;
use Cycle\Schema\Generator\Migrations\NameGeneratorInterface;
use Cycle\Schema\Generator\Migrations\Strategy\GeneratorStrategyInterface;
use Cycle\Schema\Generator\Migrations\Strategy\SingleFileStrategy;
use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;

final class MigrationsBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        TokenizerBootloader::class,
        DatabaseBootloader::class,
    ];

    protected const SINGLETONS = [
        Migrator::class => Migrator::class,
        RepositoryInterface::class => FileRepository::class,
        NameGeneratorInterface::class => [self::class, 'initNameGenerator'],
        GeneratorStrategyInterface::class => [self::class, 'initGeneratorStrategy'],
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config,
    ) {
    }

    public function init(
        EnvironmentInterface $env,
        DirectoriesInterface $dirs
    ): void {
        if (! $dirs->has('migrations')) {
            $dirs->set('migrations', $dirs->get('app') . 'migrations');
        }

        $this->config->setDefaults(
            MigrationConfig::CONFIG,
            [
                'directory' => $dirs->get('migrations'),
                'vendorDirectories' => [],
                'strategy' => SingleFileStrategy::class,
                'nameGenerator' => NameBasedOnChangesGenerator::class,
                'table' => 'migrations',
                'safe' => $env->get('SAFE_MIGRATIONS', false),
            ]
        );
    }

    public function addVendorDirectory(string $directory): void
    {
        $this->config->modify(
            MigrationConfig::CONFIG,
            new Append('vendorDirectories', null, $directory),
        );
    }

    private function initGeneratorStrategy(
        MigrationConfig $config,
        ContainerInterface $container
    ): GeneratorStrategyInterface {
        $strategy = $config->toArray()['strategy'] ?? SingleFileStrategy::class;

        return $container->get($strategy);
    }

    private function initNameGenerator(MigrationConfig $config, ContainerInterface $container): NameGeneratorInterface
    {
        $nameGenerator = $config->toArray()['nameGenerator'] ?? NameBasedOnChangesGenerator::class;

        return $container->get($nameGenerator);
    }
}
