<?php

declare(strict_types=1);

namespace Spiral\App\Controller;

use DateTimeInterface;
use Spiral\App\Entities\Role;
use Spiral\App\Entities\User;

class HomeController
{
    public function entity(User $user)
    {
        return $user->getName();
    }

    public function entity2(User $user, Role $role)
    {
        return 'ok';
    }

    public function index(): string
    {
        return 'test';
    }

    public function entityUnion(Role|User $entity): Role|User
    {
        return $entity;
    }

    public function builtInParam(string $entity): string
    {
        return $entity;
    }
}
