<?php

declare(strict_types=1);

namespace Spiral\Tests\Interceptor;

use Cycle\ORM\EntityManager;
use Spiral\App\Entities\Role;
use Spiral\Core\CoreInterface;
use Spiral\Core\Exception\ControllerException;
use Spiral\App\Controller\HomeController;
use Spiral\App\Entities\User;
use Spiral\Tests\ConsoleTest;

final class CycleInterceptorTest extends ConsoleTest
{
    public function setUp(): void
    {
        parent::setUp();
        $this->runCommandDebug('cycle:sync');


        $u = new User('Antony');
        $u->roles->add(new Role('admin'));

        $this->app->get(EntityManager::class)->persist($u)->run();
    }

    public function testInjectedInstance(): void
    {
        /** @var CoreInterface $core */
        $core = $this->app->get(CoreInterface::class);

        $this->expectException(ControllerException::class);
        $core->callAction(HomeController::class, 'entity', []);
    }

    public function testInjectedInstance1(): void
    {
        /** @var CoreInterface $core */
        $core = $this->app->get(CoreInterface::class);

        $this->expectException(ControllerException::class);
        $core->callAction(HomeController::class, 'entity', ['user' => 2]);
    }

    public function testInjectedInstance2(): void
    {
        /** @var CoreInterface $core */
        $core = $this->app->get(CoreInterface::class);

        $this->assertSame(
            'Antony',
            $core->callAction(HomeController::class, 'entity', ['user' => 1])
        );
    }

    // singular entity
    public function testInjectedInstance3(): void
    {
        /** @var CoreInterface $core */
        $core = $this->app->get(CoreInterface::class);

        $this->assertSame(
            'Antony',
            $core->callAction(HomeController::class, 'entity', ['id' => 1])
        );
    }

    public function testMultipleEntitiesButID(): void
    {
        /** @var CoreInterface $core */
        $core = $this->app->get(CoreInterface::class);

        $this->expectException(ControllerException::class);
        $core->callAction(HomeController::class, 'entity2', ['id' => 1]);
    }

    // singular entity
    public function testMultipleEntities(): void
    {
        /** @var CoreInterface $core */
        $core = $this->app->get(CoreInterface::class);

        $this->assertSame(
            'ok',
            $core->callAction(HomeController::class, 'entity2', ['user' => 1, 'role' => 1])
        );
    }

    // singular entity
    public function testBypass(): void
    {
        /** @var CoreInterface $core */
        $core = $this->app->get(CoreInterface::class);

        $this->assertSame(
            'Demo',
            $core->callAction(HomeController::class, 'entity', ['user' => new User('Demo')])
        );
    }
}
