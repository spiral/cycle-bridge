<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Spiral\Attributes\ReaderInterface;
use Spiral\Cycle\Annotated\Locator\ListenerEmbeddingsLocator;
use Spiral\Cycle\Annotated\Locator\ListenerEntityLocator;
use Spiral\Tests\BaseTest;

final class AnnotatedBootloaderTest extends BaseTest
{
    public function testGetsReader(): void
    {
        $this->assertContainerBound(ReaderInterface::class);
    }

    public function testGetsListenerEntityLocator(): void
    {
        $this->assertContainerBoundAsSingleton(ListenerEntityLocator::class, ListenerEntityLocator::class);
    }

    public function testGetsListenerEmbeddingsLocator(): void
    {
        $this->assertContainerBoundAsSingleton(ListenerEmbeddingsLocator::class, ListenerEmbeddingsLocator::class);
    }
}
