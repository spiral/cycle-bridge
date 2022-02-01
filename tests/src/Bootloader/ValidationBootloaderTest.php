<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Spiral\Core\ConfigsInterface;
use Spiral\Cycle\Validation\EntityChecker;
use Spiral\Tests\BaseTest;

final class ValidationBootloaderTest extends BaseTest
{
    public function test(): void
    {
        /** @var ConfigsInterface $configs */
        $configs = $this->app->get(ConfigsInterface::class);

        /** @var array<string, array<string, class-string|callable>> $configs */
        $configs = $configs->getConfig('validation');

        $this->assertTrue(isset($configs['checkers']['entity']));
        $this->assertSame(EntityChecker::class, $configs['checkers']['entity']);
    }
}
