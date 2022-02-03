<?php

declare(strict_types=1);

namespace Spiral\Tests;

use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Spiral\App\App;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\Environment;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\KernelInterface;
use Spiral\Config\Patch\Set;
use Spiral\Core\ConfigsInterface;
use Spiral\Core\Container;
use Spiral\Files\Files;

abstract class BaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected App $app;
    protected \Spiral\Core\Container $container;
    private array $beforeBootload = [];
    private array $afterBootload = [];

    public const ENV = [];

    public function beforeBootload(\Closure $callback): void
    {
        $this->beforeBootload[] = $callback;
    }

    public function afterBootload(\Closure $callback): void
    {
        $this->afterBootload[] = $callback;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = $this->makeApp(static::ENV);
    }

    public function getOrm(): ORMInterface
    {
        return $this->app->get(ORMInterface::class);
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->app->get(EntityManagerInterface::class);
    }

    public function getRepository(string $role): RepositoryInterface
    {
        return $this->getOrm()->getRepository($role);
    }

    public function getConfig(string $config): array
    {
        return $this->app->get(ConfigsInterface::class)->getConfig($config);
    }

    public function setConfig(string $config, mixed $data): void
    {
        $this->app->get(ConfigsInterface::class)->setDefaults(
            $config,
            $data
        );
    }

    public function updateConfig(string $key, mixed $data): void
    {
        [$config, $key] = explode('.', $key, 2);

        $this->app->get(ConfigsInterface::class)->modify(
            $config,
            new Set($key, $data)
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $fs = new Files();

        $runtime = $this->app->get(DirectoriesInterface::class)->get('runtime');
        if ($fs->isDirectory($runtime)) {
            $fs->deleteDirectory($runtime, true);
            $fs->deleteDirectory($runtime);
        }
    }

    private function makeApp(array $env = []): KernelInterface
    {
        $this->container = $container = new Container();
        $beforeBootload = $this->beforeBootload;
        $afterBootload = $this->afterBootload;

        $environment = new Environment($env);

        $root = dirname(__DIR__);

        $app = new App($this->container, [
            'root' => $root,
            'app' => $root.'/App',
            'runtime' => $root.'/runtime/tests',
            'cache' => $root.'/runtime/tests/cache',
        ]);

        // will protect any against env overwrite action
        $this->container->runScope(
            [EnvironmentInterface::class => $environment],
            \Closure::bind(function () use ($container, $beforeBootload, $afterBootload): void {
                foreach ($beforeBootload as $callback) {
                    $callback($container);
                }

                $this->bootload();
                $this->bootstrap();
            }, $app, AbstractKernel::class)
        );

        foreach ($afterBootload as $callback) {
            $callback($container);
        }

        return $app;
    }

    protected function accessProtected(object $obj, string $prop)
    {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);

        return $property->getValue($obj);
    }
}
