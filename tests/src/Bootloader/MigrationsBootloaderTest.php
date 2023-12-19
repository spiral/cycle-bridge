<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\Migrations\FileRepository;
use Cycle\Migrations\Migrator;
use Cycle\Migrations\RepositoryInterface;
use Cycle\Schema\Generator\Migrations\NameBasedOnChangesGenerator;
use Cycle\Schema\Generator\Migrations\Strategy\SingleFileStrategy;
use Spiral\Tests\BaseTest;

final class MigrationsBootloaderTest extends BaseTest
{
    public function testGetsMigrator(): void
    {
        $this->assertContainerBoundAsSingleton(Migrator::class, Migrator::class);
    }

    public function testGetsRepository(): void
    {
        $this->assertContainerBoundAsSingleton(RepositoryInterface::class, FileRepository::class);
    }

    public function testGetsDefaultConfig(): void
    {
        $config = $this->getConfig('migration');

        $this->assertDirectoryAliasDefined('migrations');
        $this->assertSame([
            'directory' => $this->getDirectoryByAlias('migrations'),
            'strategy' => SingleFileStrategy::class,
            'nameGenerator' => NameBasedOnChangesGenerator::class,
            'table' => 'migrations',
            'safe' => false,
        ], $config);
    }
}
