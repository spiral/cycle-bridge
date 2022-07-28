<?php

declare(strict_types=1);

namespace Spiral\App\Bootloader;

use Spiral\App\Repositories\RoleRepository;
use Spiral\App\Repositories\RoleRepositoryInterface;
use Spiral\Bootloader\DomainBootloader;
use Spiral\Core\CoreInterface;
use Spiral\Cycle\Interceptor\CycleInterceptor;

final class AppBootloader extends DomainBootloader
{
    protected const BINDINGS = [
        RoleRepositoryInterface::class => RoleRepository::class
    ];

    protected const SINGLETONS = [
        CoreInterface::class => [self::class, 'domainCore'],
    ];

    protected const INTERCEPTORS = [
        CycleInterceptor::class,
    ];
}
