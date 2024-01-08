<?php

declare(strict_types=1);

namespace Spiral\Tests\Interceptor;

use Spiral\App\Database\Factory\RoleFactory;
use Spiral\App\Database\Factory\UserFactory;
use Spiral\Core\CoreInterface;
use Spiral\Core\Exception\ControllerException;
use Spiral\App\Controller\HomeController;
use Spiral\App\Entities\User;
use Spiral\Tests\DatabaseTest;

final class CycleInterceptorTest extends DatabaseTest
{
    private User $contextEntity;

    public function setUp(): void
    {
        parent::setUp();
        $this->cleanIdentityMap();

        $role = RoleFactory::new(['name' => 'admin'])->makeOne();
        UserFactory::new(['name' => 'Antony'])->addRole($role)->createOne();

        $this->contextEntity = UserFactory::new(['name' => 'Contextual'])
            ->addRole(RoleFactory::new()->makeOne())
            ->createOne();
    }

    public function testCallBadAction(): void
    {
        /** @var CoreInterface $core */
        $core = $this->getContainer()->get(CoreInterface::class);

        $this->expectException(ControllerException::class);
        $this->expectExceptionMessage('Invalid action');
        $core->callAction(HomeController::class, 'no_method', []);
    }

    public function testCallBuiltInType(): void
    {
        /** @var CoreInterface $core */
        $core = $this->getContainer()->get(CoreInterface::class);

        $this->expectException(ControllerException::class);
        $this->expectExceptionMessage('invalid parameter');
        $core->callAction(HomeController::class, 'builtInParam', []);
    }

    public function testInjectedInstance(): void
    {
        /** @var CoreInterface $core */
        $core = $this->getContainer()->get(CoreInterface::class);

        $this->expectException(ControllerException::class);
        $this->expectExceptionMessage('Entity `user` can not be found');
        $core->callAction(HomeController::class, 'entity', []);
    }

    public function testInjectedInstance1(): void
    {
        /** @var CoreInterface $core */
        $core = $this->getContainer()->get(CoreInterface::class);

        $this->expectException(ControllerException::class);
        $core->callAction(HomeController::class, 'entity', ['user' => 69]);
    }

    public function testInjectedInstance2(): void
    {
        /** @var CoreInterface $core */
        $core = $this->getContainer()->get(CoreInterface::class);

        $this->assertSame(
            ['user' => 'Antony'],
            $core->callAction(HomeController::class, 'entity', ['user' => 1]),
        );
    }

    /**
     * Check cache using
     */
    public function testInjectedTheSameTwice(): void
    {
        /** @var CoreInterface $core */
        $core = $this->getContainer()->get(CoreInterface::class);

        $this->assertSame(['user' => 'Antony'], $core->callAction(HomeController::class, 'entity', ['user' => 1]));
        $this->assertSame(['user' => 'Antony'], $core->callAction(HomeController::class, 'entity', ['user' => 1]));
    }

    // singular entity
    public function testInjectedInstance3(): void
    {
        /** @var CoreInterface $core */
        $core = $this->getContainer()->get(CoreInterface::class);

        $this->assertSame(
            ['user' => 'Antony'],
            $core->callAction(HomeController::class, 'entity', ['id' => 1]),
        );
    }

    public function testMultipleEntitiesButID(): void
    {
        /** @var CoreInterface $core */
        $core = $this->getContainer()->get(CoreInterface::class);

        $this->expectException(ControllerException::class);
        $core->callAction(HomeController::class, 'entity2', ['id' => 1]);
    }

    // singular entity
    public function testMultipleEntities(): void
    {
        /** @var CoreInterface $core */
        $core = $this->getContainer()->get(CoreInterface::class);

        $this->assertSame(
            ['user' => 'Antony', 'role' => 'admin'],
            $core->callAction(HomeController::class, 'entity2', ['user' => 1, 'role' => 1]),
        );
    }

    /**
     * singular entity
     */
    public function testBypass(): void
    {
        /** @var CoreInterface $core */
        $core = $this->getContainer()->get(CoreInterface::class);

        $this->assertSame(
            ['user' => 'Demo'],
            $core->callAction(HomeController::class, 'entity', ['user' => new User('Demo')]),
        );
    }

    /**
     * Entity in the Heap
     */
    public function testInjectedTWithContext(): void
    {
        /** @var CoreInterface $core */
        $core = $this->getContainer()->get(CoreInterface::class);

        $this->assertSame(
            ['user' => 'Contextual'],
            $core->callAction(HomeController::class, 'entity', ['user' => $this->contextEntity]),
        );
    }
}
