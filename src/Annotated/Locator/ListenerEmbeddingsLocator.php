<?php

declare(strict_types=1);

namespace Spiral\Cycle\Annotated\Locator;

use Cycle\Annotated\Annotation\Embeddable;
use Cycle\Annotated\Exception\AnnotationException;
use Cycle\Annotated\Locator\Embedding;
use Cycle\Annotated\Locator\EmbeddingLocatorInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[TargetAttribute(Embeddable::class, useAnnotations: true)]
final class ListenerEmbeddingsLocator implements EmbeddingLocatorInterface, TokenizationListenerInterface
{
    /**
     * @var Embedding[]
     */
    private array $embeddings = [];
    private bool $collected = false;

    public function __construct(
        private readonly ReaderInterface $reader
    ) {
    }

    public function listen(\ReflectionClass $class): void
    {
        try {
            $attribute = $this->reader->firstClassMetadata($class, Embeddable::class);
        } catch (\Exception $e) {
            throw new AnnotationException($e->getMessage(), (int) $e->getCode(), $e);
        }

        if ($attribute !== null) {
            $this->embeddings[] = new Embedding($attribute, $class);
        }
    }

    public function finalize(): void
    {
        $this->collected = true;
    }

    public function getEmbeddings(): array
    {
        if (!$this->collected) {
            throw new AnnotationException(\sprintf('Tokenizer did not finalize %s listener.', self::class));
        }

        return $this->embeddings;
    }
}
