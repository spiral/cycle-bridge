<?php

declare(strict_types=1);

namespace Spiral\Cycle\DataGrid\Response;

use Spiral\DataGrid\GridInterface;

final class GridResponse implements \JsonSerializable, GridResponseInterface
{
    private ?GridInterface $grid = null;
    private array $data = [];
    private array $options = [];

    public function withGrid(GridInterface $grid, array $options = []): GridResponseInterface
    {
        $response = clone $this;
        $response->grid = $grid;
        $response->options = $options;
        $response->data = iterator_to_array($grid->getIterator());

        return $response;
    }

    public function jsonSerialize(): array
    {
        if ($this->grid === null) {
            return [
                'status' => 500,
                'error' => 'missing-grid-source',
            ];
        }

        $response = [
            'status' => $this->option('status', 200),
            $this->option('property', 'data') => $this->data,
        ];

        if ($this->grid->getOption(GridInterface::PAGINATOR) !== null) {
            $response['pagination'] = $this->grid->getOption(GridInterface::PAGINATOR);
        }

        if (isset($response['pagination']) && $this->grid->getOption(GridInterface::COUNT) !== null) {
            $response['pagination']['count'] = $this->grid->getOption(GridInterface::COUNT);
        }

        return $response;
    }

    private function option(string $name, $default)
    {
        return $this->options[$name] ?? $default;
    }
}
