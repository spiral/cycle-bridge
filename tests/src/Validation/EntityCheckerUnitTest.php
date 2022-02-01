<?php

declare(strict_types=1);

namespace Spiral\Tests\Validation;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Spiral\Cycle\Validation\EntityChecker;

final class EntityCheckerUnitTest extends TestCase
{
    public function testExistsWithEmptyDatabase(): void
    {
        $orm = m::mock(ORMInterface::class);
        $orm->shouldReceive('getRepository')
            ->andReturn($this->makeRepository());
        $checker = new EntityChecker($orm);

        $this->assertFalse($checker->exists(42, 'test'));
    }

    public function testExistsByPk(): void
    {
        $orm = m::mock(ORMInterface::class);
        $orm->shouldReceive('getRepository')
            ->andReturn(
                $this->makeRepository([
                    ['id' => 42, 'value' => 'test value'],
                ])
            );
        $checker = new EntityChecker($orm);

        $this->assertTrue($checker->exists(42, 'test'));
    }

    public function testExistsByCustomField(): void
    {
        $orm = m::mock(ORMInterface::class);
        $orm->shouldReceive('getRepository')
            ->andReturn(
                $this->makeRepository([
                    ['id' => 42, 'foo' => 'bar', 'value' => 'test value'],
                ])
            );
        $checker = new EntityChecker($orm);

        $this->assertTrue($checker->exists('bar', 'test', 'foo'));
        $this->assertFalse($checker->exists('baz', 'test', 'foo'));
    }

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
                foreach ($this->items as $item) {
                    if (\array_intersect_assoc($item, $scope) === $scope) {
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
        };
    }
}