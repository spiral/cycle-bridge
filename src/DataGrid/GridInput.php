<?php

declare(strict_types=1);

namespace Spiral\Cycle\DataGrid;

use Spiral\DataGrid\InputInterface;
use Spiral\Http\Request\InputManager;

final class GridInput implements InputInterface
{
    public function __construct(
        private readonly InputManager $input
    ) {
    }

    public function withNamespace(string $namespace): InputInterface
    {
        $input = clone $this;
        $input->input = $input->input->withPrefix($namespace);

        return $input;
    }

    public function hasValue(string $option): bool
    {
        return $this->input->input($option) !== null;
    }

    public function getValue(string $option, mixed $default = null): mixed
    {
        return $this->input->input($option, $default);
    }
}
