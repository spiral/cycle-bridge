<?php

declare(strict_types=1);

namespace Spiral\Tests;

use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;
use ReflectionMethod;
use Spiral\App\Bootloader\AppBootloader;
use Spiral\App\Bootloader\SyncTablesBootloader;
use Spiral\Bootloader as Framework;
use Spiral\Config\Patch\Set;
use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Core\ConfigsInterface;
use Spiral\Cycle\Bootloader as CycleBridge;
use Spiral\DataGrid\Bootloader\GridBootloader;
use Spiral\Testing\TestCase;

abstract class BaseTest extends TestCase
{
    protected function setUp(): void
    {
        $this->updateConfigFromAttribute();
        parent::setUp();
    }

    /**
     * @template TClass
     *
     * @param class-string<TClass> $attribute
     * @param null|non-empty-string $method Method name
     *
     * @return array<int, TClass>
     */
    public function getTestAttributes(string $attribute, string $method = null): array
    {
        try {
            $result = [];
            $attributes = (new ReflectionMethod($this, $method ?? $this->getName(false)))->getAttributes($attribute);
            foreach ($attributes as $attr) {
                $result[] = $attr->newInstance();
            }
            return $result;
        } catch (\Throwable) {
            return [];
        }
    }

    public function rootDirectory(): string
    {
        return dirname(__DIR__ . '/../app');
    }

    public function defineBootloaders(): array
    {
        return [
            // Framework commands
            ConsoleBootloader::class,
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
            GridBootloader::class,
            CycleBridge\DataGridBootloader::class,

            // Auth
            CycleBridge\AuthTokensBootloader::class,
            // Validation
            CycleBridge\ValidationBootloader::class,

            // Scaffolder
            CycleBridge\ScaffolderBootloader::class,

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
        $this->beforeBooting(static function (ConfigsInterface $configs) use ($config, $key, $data) {
            $configs->modify(
                $config,
                new Set($key, $data)
            );
        });
    }

    protected function updateConfigFromAttribute(): void
    {
        foreach ($this->getTestAttributes(ConfigAttribute::class) as $attribute) {
            \assert($attribute instanceof ConfigAttribute);
            $this->updateConfig($attribute->path, $attribute->closure?->__invoke() ?? $attribute->value);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->cleanUpRuntimeDirectory();
    }

    protected function accessProtected(object $obj, string $prop)
    {
        $reflection = new \ReflectionClass($obj);

        return $reflection->getProperty($prop)->getValue($obj);
    }
}
