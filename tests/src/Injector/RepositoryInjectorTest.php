<?php

declare(strict_types=1);

namespace Spiral\Tests\Injector;

use Spiral\App\Repositories\UserRepository;
use Spiral\Tests\BaseTest;

final class RepositoryInjectorTest extends BaseTest
{
    public function testInjectRepository(): void
    {
        $this->assertInstanceOf(UserRepository::class, $this->container->get(UserRepository::class));
    }
}
