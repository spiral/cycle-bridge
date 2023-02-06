<?php

declare(strict_types=1);

namespace Spiral\Tests\Cycle\Fixture;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

#[Entity]
class Bar
{
    #[Column(type: 'primary')]
    public $id;

    #[Column(type: 'string')]
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
