<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Spiral\Cycle\Auth\Token;
use Spiral\Cycle\Auth\TokenStorage as CycleStorage;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Auth\HttpAuthBootloader;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;

final class AuthTokensBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        CycleOrmBootloader::class,
        AnnotatedBootloader::class,
    ];

    public function init(
        TokenizerBootloader $tokenizer,
        HttpAuthBootloader $bootloader,
    ): void {
        $bootloader->addTokenStorage('cycle', CycleStorage::class);

        $tokenClass = new \ReflectionClass(Token::class);
        $tokenizer->addDirectory(\dirname($tokenClass->getFileName()));
    }
}
