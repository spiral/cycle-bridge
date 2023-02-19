<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Spiral\Auth\Config\AuthConfig;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Set;
use Spiral\Cycle\Auth\Token;
use Spiral\Cycle\Auth\TokenStorage as CycleStorage;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Auth\HttpAuthBootloader;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;

final class AuthTokensBootloader extends Bootloader
{
    private const TOKEN_STORAGE_NAME = 'cycle';

    protected const DEPENDENCIES = [
        CycleOrmBootloader::class,
        AnnotatedBootloader::class,
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config,
    ) {
    }

    public function init(
        TokenizerBootloader $tokenizer,
        HttpAuthBootloader $bootloader,
        EnvironmentInterface $env
    ): void {
        $bootloader->addTokenStorage(self::TOKEN_STORAGE_NAME, CycleStorage::class);

        // This is a temporary fix and backward compatibility for case when AUTH_TOKEN_STORAGE is not set.
        // TODO: Will be removed after release of spiral/framework 4.0.0
        if ($env->get('AUTH_TOKEN_STORAGE') === null) {
            $this->config->modify(
                AuthConfig::CONFIG,
                new Set('defaultStorage', self::TOKEN_STORAGE_NAME),
            );
        }

        $tokenClass = new \ReflectionClass(Token::class);
        $tokenizer->addDirectory(\dirname($tokenClass->getFileName()));
    }
}
