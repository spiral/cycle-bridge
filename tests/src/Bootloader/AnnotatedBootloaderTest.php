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
        $this->assertContainerBound(ReaderInterface::class);
    }

    public function testGetsAnnotatedEmbeddings(): void
    {
        $this->assertContainerBound(Annotated\Embeddings::class, GeneratorInterface::class);
    }

    public function testGetsAnnotatedEntities(): void
    {
        $this->assertContainerBound(Annotated\Entities::class, GeneratorInterface::class);
    }

    public function testGetsAnnotatedMergeColumns(): void
    {
        $this->assertContainerBound(Annotated\MergeColumns::class, GeneratorInterface::class);
    }

    public function testGetsAnnotatedTableInheritance(): void
    {
        $this->assertContainerBound(Annotated\TableInheritance::class, GeneratorInterface::class);
    }

    public function testGetsAnnotatedMergeIndexes(): void
    {
        $this->assertContainerBound(Annotated\MergeIndexes::class, GeneratorInterface::class);
    }
}
