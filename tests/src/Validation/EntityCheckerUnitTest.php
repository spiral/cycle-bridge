<?php

declare(strict_types=1);

namespace Spiral\Tests\Validation;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\SchemaInterface;
use Mockery as m;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Spiral\Cycle\Validation\EntityChecker;

final class EntityCheckerUnitTest extends TestCase
{
    use EntityCheckerTrait;

    public function testExistsWithEmptyDatabase(): void
    {
        $orm = $this->makeOrm(['test' => 'id']);
        $orm->shouldReceive('getRepository')
            ->andReturn($this->makeRepository());
        $checker = new EntityChecker($orm);

        $this->assertFalse($checker->exists(42, 'test'));
    }

    public function testExistsByPk(): void
    {
        $orm = $this->makeOrm(['test' => 'id']);
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
        $orm = $this->makeOrm(['test' => 'id']);
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

    public function testNonExistByArrayPk(): void
    {
        $orm = $this->makeOrm(['test' => 'id']);
        $orm->shouldReceive('getRepository')
            ->andReturn($this->makeRepository());
        $checker = new EntityChecker($orm);

        $this->assertFalse($checker->exists([42, 1], 'test'));
    }

    // public function testExistByPkMultiple(): void
    // {
    //     $orm = $this->makeOrm(['test' => 'id']);
    //     $orm->shouldReceive('getRepository')
    //         ->andReturn($this->makeRepository([
    //             ['id' => 42, 'foo' => 'bar', 'value' => 'test value'],
    //             ['id' => 1, 'foo' => 'bar', 'value' => 'test value'],
    //         ]));
    //     $checker = new EntityChecker($orm);
    //
    //     $this->assertTrue($checker->exists([42, 1], 'test', multiple: true));
    // }

    public function testNonExistsByCustomFieldMultiple(): void
    {
        $orm = $this->makeOrm(['test' => 'id']);
        $orm->shouldReceive('getRepository')
            ->andReturn(
                $this->makeRepository([
                    ['id' => 42, 'foo' => 'bar', 'value' => 'test value'],
                    ['id' => 1, 'foo' => 'baz', 'value' => 'test value'],
                ])
            );
        $checker = new EntityChecker($orm);

        $this->assertFalse($checker->exists(['bar', 'non-exist'], 'test', 'foo', multiple: true));
    }

    // public function testExistsByCustomFieldMultiple(): void
    // {
    //     $orm = $this->makeOrm(['test' => 'id']);
    //     $orm->shouldReceive('getRepository')
    //         ->andReturn(
    //             $this->makeRepository([
    //                 ['id' => 42, 'foo' => 'bar', 'value' => 'test value'],
    //                 ['id' => 1, 'foo' => 'baz', 'value' => 'test value'],
    //             ])
    //         );
    //     $checker = new EntityChecker($orm);
    //
    //     $this->assertTrue($checker->exists(['bar', 'baz'], 'test', 'foo', multiple: true));
    // }
}
