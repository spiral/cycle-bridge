<?php

declare(strict_types=1);

namespace Spiral\Tests\Annotated\Locator;

use Cycle\Annotated\Annotation\Embeddable;
use Cycle\Annotated\Exception\AnnotationException;
use Cycle\Annotated\Locator\Embedding;
use PHPUnit\Framework\TestCase;
use Spiral\App\Entities\Address;
use Spiral\App\Entities\User;
use Spiral\Attributes\AttributeReader;
use Spiral\Cycle\Annotated\Locator\ListenerEmbeddingsLocator;

final class ListenerEmbeddingsLocatorTest extends TestCase
{
    public function testListen(): void
    {
        $locator = new ListenerEmbeddingsLocator(new AttributeReader());
        $locator->listen(new \ReflectionClass(Address::class));
        $locator->finalize();

        $this->assertEquals(
            [
                new Embedding(
                    new Embeddable(),
                    new \ReflectionClass(Address::class)
                ),
            ],
            $locator->getEmbeddings());
    }

    public function testListenWithoutAttribute(): void
    {
        $locator = new ListenerEmbeddingsLocator(new AttributeReader());
        $locator->listen(new \ReflectionClass(User::class));
        $locator->finalize();

        $this->assertSame([], $locator->getEmbeddings());
    }

    public function testGetEmbeddingsWithoutFinalize(): void
    {
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage(
            \sprintf('Tokenizer did not finalize %s listener.', ListenerEmbeddingsLocator::class)
        );

        $locator = new ListenerEmbeddingsLocator(new AttributeReader());
        $locator->getEmbeddings();
    }
}
