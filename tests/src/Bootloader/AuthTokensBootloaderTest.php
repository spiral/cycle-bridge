<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Spiral\Auth\TokenStorageInterface;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Cycle\Auth\Token;
use Spiral\Cycle\Auth\TokenStorage as CycleStorage;
use Spiral\Tests\BaseTest;

final class AuthTokensBootloaderTest extends BaseTest
{
    public function testGetsTokenStorage(): void
    {
        $this->assertContainerBound(TokenStorageInterface::class, CycleStorage::class);
    }

    public function testTokenEntityShouldBeRegisterInTokenizer(): void
    {
        $config = $this->getConfig('tokenizer');
        $dirs = $this->getContainer()->get(DirectoriesInterface::class);

        $this->assertContains($dirs->get('app'), $config['directories']);
        $this->assertContains(\dirname((new \ReflectionClass(Token::class))->getFileName()), $config['directories']);
    }
}
