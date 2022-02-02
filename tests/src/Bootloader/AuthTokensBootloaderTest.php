<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Spiral\Auth\TokenStorageInterface;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Cycle\Auth\Token;
use Spiral\Tests\BaseTest;
use Spiral\Cycle\Auth\TokenStorage as CycleStorage;

final class AuthTokensBootloaderTest extends BaseTest
{
    public function testGetsTokenStorage()
    {
        $this->assertInstanceOf(
            CycleStorage::class,
            $this->app->get(TokenStorageInterface::class)
        );
    }

    public function testTokenEntityShouldBeRegisterInTokenizer()
    {
        $config = $this->getConfig('tokenizer');
        $dirs = $this->app->get(DirectoriesInterface::class);

        $this->assertContains($dirs->get('app'), $config['directories']);
        $this->assertContains(\dirname((new \ReflectionClass(Token::class))->getFileName()), $config['directories']);
    }
}
