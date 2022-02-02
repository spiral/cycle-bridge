<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\Annotated;
use Cycle\Schema\GeneratorInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tests\BaseTest;

final class AnnotatedBootloaderTest extends BaseTest
{
    public function testGetsReader(): void
    {
        $this->assertInstanceOf(
            ReaderInterface::class,
            $this->app->get(ReaderInterface::class)
        );
    }

    public function testGetsAnnotatedEmbeddings(): void
    {
        $this->assertInstanceOf(
            GeneratorInterface::class,
            $this->app->get(Annotated\Embeddings::class)
        );
    }

    public function testGetsAnnotatedEntities(): void
    {
        $this->assertInstanceOf(
            GeneratorInterface::class,
            $this->app->get(Annotated\Entities::class)
        );
    }

    public function testGetsAnnotatedMergeColumns(): void
    {
        $this->assertInstanceOf(
            GeneratorInterface::class,
            $this->app->get(Annotated\MergeColumns::class)
        );
    }

    public function testGetsAnnotatedTableInheritance(): void
    {
        $this->assertInstanceOf(
            GeneratorInterface::class,
            $this->app->get(Annotated\TableInheritance::class)
        );
    }

    public function testGetsAnnotatedMergeIndexes(): void
    {
        $this->assertInstanceOf(
            GeneratorInterface::class,
            $this->app->get(Annotated\MergeIndexes::class)
        );
    }
}
