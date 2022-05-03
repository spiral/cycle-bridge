<?php

declare(strict_types=1);

namespace Spiral\Cycle;

use Cycle\Database\Driver\DriverInterface;
use Cycle\Database\LoggerFactoryInterface;
use Cycle\Database\NamedInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Spiral\Core\ConfigsInterface;
use Spiral\Logger\LogsInterface;

final class LoggerFactory implements LoggerFactoryInterface
{
    private array $config;

    public function __construct(
        private readonly ContainerInterface $container,
        ConfigsInterface $configs
    ) {
        $this->config = $configs->getConfig('database')['logger'] ?? [];
    }

    public function getLogger(DriverInterface $driver = null): LoggerInterface
    {
        if (! $this->container->has(LogsInterface::class)) {
            return new NullLogger();
        }

        $channel = $driver::class;

        if ($driver instanceof NamedInterface && isset($this->config['drivers'][$driver->getName()])) {
            $channel = $this->config['drivers'][$driver->getName()];
        } else if (isset($this->config['drivers'][$driver::class])) {
            $channel = $this->config['drivers'][$driver::class];
        } else if (isset($this->config['default']) && $this->config['default'] !== null) {
            $channel = $this->config['default'];
        }

        return $this->container->get(LogsInterface::class)->getLogger($channel);
    }
}
