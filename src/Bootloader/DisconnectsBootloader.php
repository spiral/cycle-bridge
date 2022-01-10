<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Cycle\Database\DatabaseManager;
use Cycle\Database\DatabaseProviderInterface;
use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\FinalizerInterface;

/**
 * Close all the connections after each serve() cycle.
 */
final class DisconnectsBootloader extends Bootloader
{
    public function boot(FinalizerInterface $finalizer, ContainerInterface $container): void
    {
        $finalizer->addFinalizer(
            function () use ($container): void {
                /** @var DatabaseManager $dbal */
                $dbal = $container->get(DatabaseManager::class);
                foreach ($dbal->getDrivers() as $driver) {
                    $driver->disconnect();
                }
            }
        );
    }
}
