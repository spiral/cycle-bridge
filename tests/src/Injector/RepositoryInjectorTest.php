<?php

declare(strict_types=1);

namespace Spiral\Tests\Injector;

use Cycle\ORM\RepositoryInterface;
use Spiral\App\Repositories\UserRepository;
use Spiral\Tests\BaseTest;

final class RepositoryInjectorTest extends BaseTest
{
    public function testInjectRepository(): void
    {
        # todo replace to $this->assertAutowireable
        $this->assertInstanceOf(UserRepository::class, $this->getContainer()->get(UserRepository::class));
    }
}
