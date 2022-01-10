<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\Database\DatabaseInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\Cycle\Bootloader\DisconnectsBootloader;
use Spiral\Tests\TestCase;

final class DisconnectsBootloaderTest extends TestCase
{
    public function testConnected(): void
    {
        $db = $this->app->get(DatabaseInterface::class);

        $db->begin();
        $this->assertTrue($db->getDriver(DatabaseInterface::READ)->isConnected());
        $db->rollback();
    }

    public function testDisconnected(): void
    {
        $db = $this->app->get(DatabaseInterface::class);

        $db->begin();
        $this->assertTrue($db->getDriver(DatabaseInterface::READ)->isConnected());
        $db->rollback();

        $this->app->getBootloadManager()->bootload([
            DisconnectsBootloader::class
        ]);

        $this->assertTrue($db->getDriver(DatabaseInterface::READ)->isConnected());
        $this->app->get(FinalizerInterface::class)->finalize();
        $this->assertFalse($db->getDriver(DatabaseInterface::READ)->isConnected());
    }
}
