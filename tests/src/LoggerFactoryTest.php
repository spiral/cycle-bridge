<?php

declare(strict_types=1);

namespace Spiral\Tests;

use Cycle\Database\Driver\DriverInterface;
use Cycle\Database\Driver\MySQL\MySQLDriver;
use Cycle\Database\NamedInterface;
use Mockery as m;
use Psr\Log\NullLogger;
use Spiral\Core\ConfigsInterface;
use Spiral\Cycle\LoggerFactory;
use Spiral\Logger\LogsInterface;

final class LoggerFactoryTest extends BaseTest
{
    protected ConfigsInterface $config;
    private LogsInterface $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = m::mock(LogsInterface::class);
        $this->config = m::mock(ConfigsInterface::class);
        $this->getContainer()->bind(LogsInterface::class, $this->logger);
    }

    public function testIfLogsInterfaceIsNotRegisteredNullLoggerShouldBeUsed(): void
    {
        $this->getContainer()->removeBinding(LogsInterface::class);

        $this->config->shouldReceive('getConfig')->once()->with('database')->andReturn([]);
        $factory = new LoggerFactory($this->getContainer(), $this->config);

        $driver = m::mock(DriverInterface::class);

        $this->assertInstanceOf(NullLogger::class, $factory->getLogger($driver));
    }

    public function testGetsChannelByDriverName(): void
    {
        $driver = m::mock(DriverInterface::class, NamedInterface::class);
        $driver->shouldReceive('getName')->twice()->andReturn('runtime');

        $this->config->shouldReceive('getConfig')->once()->with('database')->andReturn([
            'logger' => [
                'default' => 'foo',
                'drivers' => [
                    'runtime' => 'bar',
                    $driver::class => 'baz'
                ],
            ]
        ]);

        $this->logger->shouldReceive('getLogger')
            ->once()
            ->with('bar')
            ->andReturn($logger = new NullLogger());

        $factory = new LoggerFactory($this->getContainer(), $this->config);
        $this->assertSame($logger, $factory->getLogger($driver));
    }

    public function testGetsChannelByDriverClass(): void
    {
        $driver = m::mock(DriverInterface::class, NamedInterface::class);
        $driver->shouldReceive('getName')->once()->andReturn('test');

        $this->config->shouldReceive('getConfig')->once()->with('database')->andReturn([
            'logger' => [
                'default' => 'foo',
                'drivers' => [
                    'runtime' => 'bar',
                    $driver::class => 'baz'
                ],
            ]
        ]);

        $this->logger->shouldReceive('getLogger')
            ->once()
            ->with('baz')
            ->andReturn($logger = new NullLogger());

        $factory = new LoggerFactory($this->getContainer(), $this->config);
        $this->assertSame($logger, $factory->getLogger($driver));
    }

    public function testGetsDefaultChannel(): void
    {
        $driver = m::mock(DriverInterface::class, NamedInterface::class);
        $driver->shouldReceive('getName')->once()->andReturn('test');

        $this->config->shouldReceive('getConfig')->once()->with('database')->andReturn([
            'logger' => [
                'default' => 'foo',
                'drivers' => [
                    'runtime' => 'bar',
                    MySQLDriver::class => 'baz'
                ],
            ]
        ]);

        $this->logger->shouldReceive('getLogger')
            ->once()
            ->with('foo')
            ->andReturn($logger = new NullLogger());

        $factory = new LoggerFactory($this->getContainer(), $this->config);
        $this->assertSame($logger, $factory->getLogger($driver));
    }

    public function testGetsDriverClassChannel(): void
    {
        $driver = m::mock(DriverInterface::class, NamedInterface::class);
        $driver->shouldReceive('getName')->once()->andReturn('test');

        $this->config->shouldReceive('getConfig')->once()->with('database')->andReturn([
            'logger' => [
                'default' => null,
                'drivers' => [
                    'runtime' => 'bar',
                    MySQLDriver::class => 'baz'
                ],
            ]
        ]);

        $this->logger->shouldReceive('getLogger')
            ->once()
            ->with($driver::class)
            ->andReturn($logger = new NullLogger());

        $factory = new LoggerFactory($this->getContainer(), $this->config);
        $this->assertSame($logger, $factory->getLogger($driver));
    }
}
