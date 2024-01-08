<?php

declare(strict_types=1);

namespace Spiral\Tests\Filter;

use Spiral\App\Database\Factory\RoleFactory;
use Spiral\App\Database\Factory\UserFactory;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Tests\DatabaseTest;

final class FilterTest extends DatabaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanIdentityMap();
    }

    public function testResolveEntity(): void
    {
        $role = RoleFactory::new()->makeOne();
        UserFactory::new()->addRole($role)->createOne();

        $response = $this->fakeHttp()->post('/role', [
            'role' => $role->id,
            'name' => 'test'
        ]);

        $response->assertBodySame(\json_encode([
            'name' => 'test',
            'role' => $role->name,
            'id' => $role->id,
        ]));
    }

    public function testResolveNonExistsEntity(): void
    {
        $this->expectException(ValidationException::class);

        $this->fakeHttp()->post('/role', [
            'role' => 2,
            'name' => 'test'
        ]);
    }
}
