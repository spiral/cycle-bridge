<?php

declare(strict_types=1);

namespace Spiral\Cycle\Scaffolder\Declaration;

use Cycle\ORM\Select\Repository;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\DependedInterface;

class RepositoryDeclaration extends ClassDeclaration implements DependedInterface
{
    public function __construct(string $name, string $comment = '')
    {
        parent::__construct($name, 'Repository', [], $comment);
    }

    public function getDependencies(): array
    {
        return [Repository::class => null];
    }
}
