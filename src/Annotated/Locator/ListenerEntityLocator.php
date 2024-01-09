<?php

declare(strict_types=1);

namespace Spiral\Cycle\Annotated\Locator;

use Cycle\Annotated\Annotation\Entity as Attribute;
use Cycle\Annotated\Exception\AnnotationException;
use Cycle\Annotated\Locator\Entity;
use Cycle\Annotated\Locator\EntityLocatorInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[TargetAttribute(Attribute::class, useAnnotations: true)]
final class ListenerEntityLocator implements EntityLocatorInterface, TokenizationListenerInterface
{
    /**
     * @var Entity[]
     */
    private array $entities = [];
    private bool $collected = false;

    public function __construct(
        private readonly ReaderInterface $reader
    ) {
    }

    public function listen(\ReflectionClass $class): void
    {
        try {
            /** @var Attribute $attribute */
            $attribute = $this->reader->firstClassMetadata($class, Attribute::class);
        } catch (\Exception $e) {
            throw new AnnotationException($e->getMessage(), (int) $e->getCode(), $e);
        }

        if ($attribute !== null) {
            $this->entities[] = new Entity($attribute, $class);
        }
    }

    public function finalize(): void
    {
        $this->collected = true;
    }

    public function getEntities(): array
    {
        if (!$this->collected) {
            throw new AnnotationException(\sprintf('Tokenizer did not finalize %s listener.', self::class));
        }

        return $this->entities;
    }
}
