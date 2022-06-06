<?php

declare(strict_types=1);

namespace Spiral\Cycle\Scaffolder\Declaration;

use Cycle\ORM\Select\Repository;
use Spiral\Scaffolder\Declaration\AbstractDeclaration;

class RepositoryDeclaration extends AbstractDeclaration
{
    public const TYPE = 'repository';

    public function declare(): void
    {
        $this->namespace->addUse(Repository::class);

        $this->class->setExtends(Repository::class);
    }
}
