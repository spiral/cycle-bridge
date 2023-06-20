<?php

declare(strict_types=1);

namespace Spiral\App\Entities;

use Cycle\Annotated\Annotation\Embeddable;
use Cycle\Annotated\Annotation\Column;

#[Embeddable]
class Address
{
    #[Column(type: 'string')]
    public string $country;

    #[Column(type: 'string(32)')]
    public string $city;

    #[Column(type: 'string(100)')]
    public string $address;
}
