<?php

declare(strict_types=1);

namespace Spiral\Cycle;

use Cycle\Database\Driver\DriverInterface;
use Cycle\Database\LoggerFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Spiral\Core\ConfigsInterface;
use Spiral\Logger\LogsInterface;

final class LoggerFactory implements LoggerFactoryInterface
{
    private array $config;

    public function __construct(
        private ContainerInterface $container,
        ConfigsInterface $configs
    ) {
        $this->config = $configs->getConfig('database')['logger'] ?? [];
    }

    public function getLogger(DriverInterface $driver = null): LoggerInterface
    {
        if (! $this->container->has(LogsInterface::class)) {
            return new NullLogger();
        }

        $channel = $this->config['drivers'][strtolower($driver->getType())]
            ?? $this->config['default']
            ?? $driver::class;

        return $this->container->get(LogsInterface::class)->getLogger($channel);
    }
}
