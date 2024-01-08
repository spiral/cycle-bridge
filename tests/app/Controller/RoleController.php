<?php

declare(strict_types=1);

namespace Spiral\App\Controller;

use Spiral\App\Controller\Filter\RoleFilter;
use Spiral\App\Entities\Role;
use Spiral\Router\Annotation\Route;

final class RoleController
{
    #[Route(route: "/role", methods: ["POST"])]
    public function create(RoleFilter $filter): array
    {
        return [
            'name' => $filter->name,
            'role' => $filter->role->name,
            'id' => $filter->role->id,
        ];
    }

    #[Route(route: "/role/<role>", methods: ["GET"])]
    public function show(Role $role): array
    {
        return [
            'name' => $role->name,
            'id' => $role->id,
        ];
    }
}
