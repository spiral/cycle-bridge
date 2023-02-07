<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Spiral\Auth\Config\AuthConfig;
use Spiral\Cycle\Auth\Token;
use Spiral\Cycle\Auth\TokenStorage;
use Spiral\Tests\BaseTest;

final class AuthTokensBootloaderTest extends BaseTest
{
    public function testGetsTokenStorage(): void
    {
        $storages = $this->getConfig(AuthConfig::CONFIG)['storages'];
        $this->assertSame(TokenStorage::class, $storages['cycle']);
    }

    public function testTokenEntityShouldBeRegisterInTokenizer(): void
    {
        $config = $this->getConfig('tokenizer');

        $this->assertDirectoryAliasDefined('app');
        $this->assertContains(\dirname((new \ReflectionClass(Token::class))->getFileName()), $config['directories']);
    }
}
