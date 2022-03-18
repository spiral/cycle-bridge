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
use Spiral\Tests\ConfigAttribute;

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
        $this->assertContainerBoundAsSingleton(DatabaseProviderInterface::class, DatabaseManager::class);
        $this->assertContainerBoundAsSingleton(DatabaseManager::class, DatabaseManager::class);
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

    #[ConfigAttribute(path: 'database.logger.default', value: 'default')]
    #[ConfigAttribute(path: 'database.logger.drivers', value: ['foo' => 'bar'])]
    public function testGetDefaultDriverLogger(): void
    {
        $this->runGetterTest('default');
    }

    #[ConfigAttribute(path: 'database.logger.default', value: 'default')]
    #[ConfigAttribute(path: 'database.logger.drivers', value: ['test' => 'bar'])]
    public function testGetBarDriverLogger(): void
    {
        $this->runGetterTest('bar');
    }

    private function runGetterTest(string $driverChannel): void
    {
        $logger = $this->mockContainer(LogsInterface::class);

        $logger->shouldReceive('getLogger')
            ->once()
            ->with($driverChannel)
            ->andReturn($log = m::mock(LoggerInterface::class));

        $log->shouldReceive('info')
            ->once()->with('hello world');

        $driver = $this->getContainer()->get(DatabaseInterface::class)->getDriver();
        \assert($driver instanceof DriverInterface);

        $this->accessProtected($driver, 'logger')->info('hello world');
    }
}
