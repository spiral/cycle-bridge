<?php

declare(strict_types=1);

namespace Spiral\Tests;

use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;
use Spiral\App\Bootloader\AppBootloader;
use Spiral\App\Bootloader\SyncTablesBootloader;
use Spiral\Bootloader as Framework;
use Spiral\Config\Patch\Set;
use Spiral\Core\ConfigsInterface;
use Spiral\Cycle\Bootloader as CycleBridge;

abstract class BaseTest extends \Spiral\Testing\TestCase
{
    public function rootDirectory(): string
    {
        return dirname(__DIR__ . '/../App');
    }

    public function defineBootloaders(): array
    {
        return [
            // Framework commands
            Framework\ConsoleBootloader::class,
            Framework\CommandBootloader::class,

            // Databases
            CycleBridge\DatabaseBootloader::class,
            CycleBridge\MigrationsBootloader::class,

            // ORM
            CycleBridge\SchemaBootloader::class,
            CycleBridge\CycleOrmBootloader::class,
            CycleBridge\AnnotatedBootloader::class,
            CycleBridge\CommandBootloader::class,

            SyncTablesBootloader::class,

            // DataGrid
            CycleBridge\DataGridBootloader::class,

            // Auth
            CycleBridge\AuthTokensBootloader::class,
            // Validation
            CycleBridge\ValidationBootloader::class,

            // App
            AppBootloader::class,
        ];
    }

    public function getOrm(): ORMInterface
    {
        return $this->getContainer()->get(ORMInterface::class);
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get(EntityManagerInterface::class);
    }

    public function getRepository(string $role): RepositoryInterface
    {
        return $this->getOrm()->getRepository($role);
    }

    public function updateConfig(string $key, mixed $data): void
    {
        [$config, $key] = explode('.', $key, 2);

        $this->getContainer()->get(ConfigsInterface::class)->modify(
            $config,
            new Set($key, $data)
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->cleanUpRuntimeDirectory();
        // $fs = new Files();
        //
        // $runtime = $this->getContainer()->get(DirectoriesInterface::class)->get('runtime');
        // if ($fs->isDirectory($runtime)) {
        //     $fs->deleteDirectory($runtime, true);
        //     $fs->deleteDirectory($runtime);
        // }
    }

    protected function accessProtected(object $obj, string $prop)
    {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);

        return $property->getValue($obj);
    }
}
