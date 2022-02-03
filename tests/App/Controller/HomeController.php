<?php

declare(strict_types=1);

namespace Spiral\App\Controller;

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
}
