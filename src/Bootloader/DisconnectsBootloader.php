<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Cycle\Database\DatabaseManager;
use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\FinalizerInterface;

/**
 * Close all the connections after each serve() cycle.
 */
final class DisconnectsBootloader extends Bootloader
{
    public function init(FinalizerInterface $finalizer, ContainerInterface $container): void
    {
        $finalizer->addFinalizer(
            function (bool $terminate) use ($container): void {
                if ($terminate) {
                    return;
                }

                /** @var DatabaseManager $dbal */
                $dbal = $container->get(DatabaseManager::class);
                foreach ($dbal->getDrivers() as $driver) {
                    $driver->disconnect();
                }
            }
        );
    }
}
