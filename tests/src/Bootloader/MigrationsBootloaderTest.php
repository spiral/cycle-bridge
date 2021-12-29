<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\Migrations\FileRepository;
use Cycle\Migrations\Migrator;
use Cycle\Migrations\RepositoryInterface;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Tests\TestCase;

final class MigrationsBootloaderTest extends TestCase
{
    public function testGetsMigrator(): void
    {
        $this->assertInstanceOf(
            Migrator::class,
            $this->app->get(Migrator::class)
        );
    }

    public function testGetsRepository(): void
    {
        $this->assertInstanceOf(
            FileRepository::class,
            $this->app->get(RepositoryInterface::class)
        );
    }

    public function testGetsDefaultConfig(): void
    {
        $config = $this->getConfig('migration');
        $dirs = $this->app->get(DirectoriesInterface::class);

        $this->assertSame([
            'directory' => $dirs->get('migrations'),
            'table' => 'migrations',
            'safe' => false,
        ], $config);
    }
}
