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
    use EntityCheckerTrait;

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
}