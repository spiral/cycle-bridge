<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\Database\DatabaseInterface;
use Spiral\Boot\BootloadManager;
use Spiral\Boot\FinalizerInterface;
use Spiral\Cycle\Bootloader\DisconnectsBootloader;
use Spiral\Tests\BaseTest;

final class DisconnectsBootloaderTest extends BaseTest
{
    public function testConnected(): void
    {
        $db = $this->getContainer()->get(DatabaseInterface::class);

        $db->begin();
        $this->assertTrue($db->getDriver(DatabaseInterface::READ)->isConnected());
        $db->rollback();
    }

    public function testDisconnected(): void
    {
        $db = $this->getContainer()->get(DatabaseInterface::class);

        $db->begin();
        $this->assertTrue($db->getDriver(DatabaseInterface::READ)->isConnected());
        $db->rollback();

        $this->getContainer()->get(BootloadManager::class)->bootload([
            DisconnectsBootloader::class
        ]);

        $this->assertTrue($db->getDriver(DatabaseInterface::READ)->isConnected());
        $this->getContainer()->get(FinalizerInterface::class)->finalize();
        $this->assertFalse($db->getDriver(DatabaseInterface::READ)->isConnected());
    }
}
