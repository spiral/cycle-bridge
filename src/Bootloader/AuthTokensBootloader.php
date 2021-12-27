<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Spiral\Cycle\Auth\Token;
use Spiral\Cycle\Auth\TokenStorage as CycleStorage;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Auth\HttpAuthBootloader;
use Spiral\Bootloader\TokenizerBootloader;

final class AuthTokensBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        HttpAuthBootloader::class,
        CycleOrmBootloader::class,
        AnnotatedBootloader::class,
    ];

    protected const SINGLETONS = [
        TokenStorageInterface::class => CycleStorage::class,
    ];

    public function boot(TokenizerBootloader $tokenizer): void
    {
        $tokenClass = new \ReflectionClass(Token::class);
        $tokenizer->addDirectory(dirname($tokenClass->getFileName()));
    }
}
