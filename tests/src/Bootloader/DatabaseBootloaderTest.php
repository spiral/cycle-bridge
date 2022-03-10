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
use Spiral\Tests\BaseTest;

final class DatabaseBootloaderTest extends BaseTest
{
    protected function setUp(): void
    {
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

        parent::setUp();
    }

    public function testGetsDatabaseManager(): void
    {
        $this->assertContainerBound(DatabaseProviderInterface::class, DatabaseManager::class);
    }

    public function testGetsDatabase(): void
    {
        $this->assertInstanceOf(
            Database::class,
            $database = $this->getContainer()->get(DatabaseInterface::class)
        );
        \assert($database instanceof DatabaseInterface);

        $this->assertSame('default', $database->getName());
        $this->assertSame('SQLite', $database->getType());
    }

    /**
     * @dataProvider driverLoggerDataProvider
     */
    public function testGetsDriverLogger(string $driverChannel, array $drivers): void
    {
        $logger = $this->mockContainer(LogsInterface::class);

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
        $driver = $this->getContainer()->get(DatabaseInterface::class)->getDriver();

        $this->accessProtected($driver, 'logger')->info('hello world');
    }

    public function driverLoggerDataProvider(): array
    {
        return [
            'default' => [
                'default',
                ['foo' => 'bar'],
            ],
            'driver' => [
                'bar',
                ['test' => 'bar'],
            ],
        ];
    }
}
