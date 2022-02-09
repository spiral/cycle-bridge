<?php

declare(strict_types=1);

namespace Spiral\Tests\Validation;

use Cycle\Database\Injection\Parameter;
use Cycle\ORM\RepositoryInterface;

trait EntityCheckerTrait
{
    /**
     * @param array<int, non-empty-array<non-empty-string, mixed>> $items
     */
    private function makeRepository(array $items = [], string $pk = 'id'): RepositoryInterface
    {
        return new class($items, $pk) implements RepositoryInterface {
            /**
             * @param array<int, non-empty-array<non-empty-string, mixed>> $items
             */
            public function __construct(
                private array $items,
                private string $pk
            ) {
            }

            public function findByPK(mixed $id): ?object
            {
                foreach ($this->items as $item) {
                    if ($item[$this->pk] === $id) {
                        return (object)$item;
                    }
                }
                return null;
            }

            public function findOne(array $scope = []): ?object
            {
                \ksort($scope);
                foreach ($this->items as $item) {
                    $result = \array_intersect_assoc($item, $scope);
                    \ksort($result);
                    if ($result === $scope) {
                        return (object)$item;
                    }
                }
                return null;
            }

            public function findAll(array $scope = []): iterable
            {
                $result = [];
                foreach ($this->items as $item) {
                    if (\array_intersect_assoc($item, $scope) === $scope) {
                        $result[] = (object)$item;
                    }
                }
                return $result;
            }

            public function select(): object
            {
                return new class($this->items) {
                    /**
                     * @param array<int, non-empty-array<non-empty-string, mixed>> $items
                     */
                    public function __construct(
                        private array $items
                    ) {
                    }

                    public function where(string $field, string $operator, Parameter $parameter): self
                    {
                        $this->items = array_filter(
                            $this->items,
                            fn(mixed $value) => \in_array($value[$field], (array)$parameter->getValue(), true)
                        );

                        return $this;
                    }

                    public function count(): int
                    {
                        return \count($this->items);
                    }
                };
            }
        };
    }
}