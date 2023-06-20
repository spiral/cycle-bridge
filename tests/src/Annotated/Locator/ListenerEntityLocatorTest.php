<?php

declare(strict_types=1);

namespace Spiral\Tests\Annotated\Locator;

use Cycle\Annotated\Exception\AnnotationException;
use Cycle\Annotated\Locator\Entity;
use PHPUnit\Framework\TestCase;
use Spiral\App\Entities\User;
use Spiral\App\Repositories\UserRepository;
use Spiral\Attributes\AttributeReader;
use Spiral\Cycle\Annotated\Locator\ListenerEntityLocator;

final class ListenerEntityLocatorTest extends TestCase
{
    public function testListen(): void
    {
        $locator = new ListenerEntityLocator(new AttributeReader());
        $locator->listen(new \ReflectionClass(User::class));
        $locator->finalize();

        $this->assertEquals(
            [
                new Entity(
                    new \Cycle\Annotated\Annotation\Entity(repository: UserRepository::class),
                    new \ReflectionClass(User::class)
                ),
            ],
            $locator->getEntities());
    }

    public function testListenWithoutAttribute(): void
    {
        $locator = new ListenerEntityLocator(new AttributeReader());
        $locator->listen(new \ReflectionClass(\stdClass::class));
        $locator->finalize();

        $this->assertSame([], $locator->getEntities());
    }

    public function testGetEntitiesWithoutFinalize(): void
    {
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage(
            \sprintf('Tokenizer did not finalize %s listener.', ListenerEntityLocator::class)
        );

        $locator = new ListenerEntityLocator(new AttributeReader());
        $locator->getEntities();
    }
}
