<?php

declare(strict_types=1);

namespace Spiral\Tests\Injector;

use Spiral\App\Repositories\RoleRepository;
use Spiral\App\Repositories\RoleRepositoryInterface;
use Spiral\App\Repositories\UserRepository;
use Spiral\Tests\BaseTest;

final class RepositoryInjectorTest extends BaseTest
{
    public function testInjectRepository(): void
    {
        # todo replace to $this->assertAutowireable
        $this->assertInstanceOf(UserRepository::class, $this->getContainer()->get(UserRepository::class));
    }

    public function testInjectRepositoryInterface(): void
    {
        $this->assertInstanceOf(RoleRepository::class, $this->getContainer()->get(RoleRepositoryInterface::class));
    }
}
