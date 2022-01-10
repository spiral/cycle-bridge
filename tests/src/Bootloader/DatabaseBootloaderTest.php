<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\Database\Config;
use Cycle\Database\Database;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseManager;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\Database\Driver\DriverInterface;
use Mockery as m;
use Psr\Log\LoggerInterface;
use Spiral\Logger\LogsInterface;
use Spiral\Tests\TestCase;

final class DatabaseBootloaderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->updateConfig('database.default', 'default');
        $this->updateConfig('database.databases', [
            'default' => [
                'driver' => 'test',
            ],
        ]);
        $this->updateConfig('database.drivers', [
            'test' => new Config\SQLiteDriverConfig(
                connection: new Config\SQLite\MemoryConnectionConfig(),
            ),
        ]);
    }

    public function testGetsDatabaseManager(): void
    {
        $this->assertInstanceOf(
            DatabaseManager::class,
            $this->app->get(DatabaseProviderInterface::class)
        );
    }

    public function testGetsDatabase(): void
    {
        /** @var DatabaseInterface $database */
        $this->assertInstanceOf(
            Database::class,
            $database = $this->app->get(DatabaseInterface::class)
        );

        $this->assertSame('default', $database->getName());
        $this->assertSame('SQLite', $database->getType());
    }

    /**
     * @dataProvider driverLoggerDataProvider
     */
    public function testGetsDriverLogger(string $driverChannel, array $drivers): void
    {
        $this->container->bind(
            LogsInterface::class,
            $logger = m::mock(LogsInterface::class)
        );

        $logger->shouldReceive('getLogger')
            ->once()
            ->with($driverChannel)
            ->andReturn($log = m::mock(LoggerInterface::class));

        $log->shouldReceive('info')
            ->once()->with('hello world');

        $this->updateConfig('database.logger', [
            'default' => 'default',
            'drivers' => $drivers,
        ]);

        /** @var DriverInterface $driver */
        $driver = $this->app->get(DatabaseInterface::class)->getDriver();

        $this->accessProtected($driver, 'logger')->info('hello world');
    }

    public function driverLoggerDataProvider(): array
    {
        return [
            'default' => [
                'default', ['foo' => 'bar']
            ],
            'driver' => [
                'bar', ['test' => 'bar']
            ]
        ];
    }
}
