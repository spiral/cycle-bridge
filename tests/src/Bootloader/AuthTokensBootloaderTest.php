<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Spiral\Auth\TokenStorageInterface;
use Spiral\Cycle\Auth\Token;
use Spiral\Cycle\Auth\TokenStorage as CycleStorage;
use Spiral\Tests\BaseTest;

final class AuthTokensBootloaderTest extends BaseTest
{
    public function testGetsTokenStorage(): void
    {
        $this->assertContainerBoundAsSingleton(TokenStorageInterface::class, CycleStorage::class);
    }

    public function testTokenEntityShouldBeRegisterInTokenizer(): void
    {
        $config = $this->getConfig('tokenizer');

        $this->assertDirectoryAliasDefined('app');
        $this->assertContains(\dirname((new \ReflectionClass(Token::class))->getFileName()), $config['directories']);
    }
}
