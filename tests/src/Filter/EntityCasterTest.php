<?php

declare(strict_types=1);

namespace Spiral\Tests\Filter;

use Spiral\App\Controller\Filter\RoleFilter;
use Spiral\App\Database\Factory\RoleFactory;
use Spiral\App\Database\Factory\UserFactory;
use Spiral\Cycle\Filter\EntityCaster;
use Spiral\Exceptions\ExceptionReporterInterface;
use Spiral\Filters\Exception\SetterException;
use Spiral\Tests\DatabaseTest;

final class EntityCasterTest extends DatabaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanIdentityMap();
    }

    public function testExistsRole(): void
    {
        $filter = new \ReflectionClass(RoleFilter::class);

        $caster = new EntityCaster(
            $this->getContainer(),
            $this->getContainer()->get(ExceptionReporterInterface::class)
        );

        $role = RoleFactory::new()->makeOne();
        UserFactory::new()->addRole($role)->createOne();

        $property = $filter->getProperty('role');

        $this->assertTrue($caster->supports($property->getType()));

        $caster->setValue($obj = $filter->newInstance(), $property, $role->id);

        $this->assertSame($role->name, $obj->role->name);
        $this->assertSame($role->id, $obj->role->id);
    }

    public function testNonExistRole(): void
    {
        $this->expectException(SetterException::class);
        $this->expectExceptionMessage('Unable to find entity `role` by primary key "1"');

        $filter = new \ReflectionClass(RoleFilter::class);

        $caster = new EntityCaster(
            $this->getContainer(),
            $this->getContainer()->get(ExceptionReporterInterface::class)
        );

        $property = $filter->getProperty('role');

        $this->assertTrue($caster->supports($property->getType()));

        $caster->setValue($obj = $filter->newInstance(), $property, 1);
    }

    public function testNonExistNullableRole(): void
    {
        $filter = new \ReflectionClass(RoleFilter::class);

        $caster = new EntityCaster(
            $this->getContainer(),
            $this->getContainer()->get(ExceptionReporterInterface::class)
        );

        $property = $filter->getProperty('nullableRole');

        $this->assertTrue($caster->supports($property->getType()));

        $caster->setValue($obj = $filter->newInstance(), $property, 1);

        $this->assertNull($obj->nullableRole);
    }
}
