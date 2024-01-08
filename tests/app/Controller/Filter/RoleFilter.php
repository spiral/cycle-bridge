<?php

declare(strict_types=1);

namespace Spiral\App\Controller\Filter;

use Spiral\App\Entities\Role;
use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Model\Filter;

final class RoleFilter extends Filter
{
    #[Post]
    public string $name;

    #[Post]
    public Role $role;

    #[Post]
    public ?Role $nullableRole;
}
