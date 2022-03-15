<?php

declare(strict_types=1);

namespace Spiral\Tests\Validation;

use Cycle\Database\Injection\Parameter;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Select\Repository;
use Mockery as m;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

trait EntityCheckerTrait
{
    private function makeOrm(array $primaryKeys = []): ORMInterface|MockInterface|LegacyMockInterface
    {
        $schema = m::mock(SchemaInterface::class);
        $orm = m::mock(ORMInterface::class);

        foreach ($primaryKeys as $role => $pk) {
            $schema->shouldReceive('define')
                ->withArgs([$role, SchemaInterface::PRIMARY_KEY])
                ->andReturn($pk);
        }
        $orm->shouldReceive('getSchema')
            ->andReturn($schema);

        return $orm;
    }

    /**
     * @param array<int, non-empty-array<non-empty-string, mixed>> $items
     */
    private function makeRepository(array $items = [], array|string $pk = 'id'): RepositoryInterface
    {
        return new class($items, (array)$pk) extends Repository {
            /**
             * @param array<int, non-empty-array<non-empty-string, mixed>> $items
             */
            public function __construct(
                private array $items,
                private array $pk
            ) {
            }

            public function findByPK(mixed $id): ?object
            {
                $id = (array)$id;
                foreach ($this->items as $item) {
                    foreach ($this->pk as $i => $pk) {
                        if (!isset($id[$i]) || $item[$pk] !== $id[$i]) {
                            continue 2;
                        }
                    }
                    return (object)$item;
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

            public function findAll(array $scope = [], array $orderBy = []): iterable
            {
                $result = [];
                foreach ($this->items as $item) {
                    if (\array_intersect_assoc($item, $scope) === $scope) {
                        $result[] = (object)$item;
                    }
                }
                return $result;
            }

            public function select(): Select
            {
                return new class($this->items) extends Select {
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

                    public function count(string $column = null): int
                    {
                        return \count($this->items);
                    }
                };
            }
        };
    }
}
