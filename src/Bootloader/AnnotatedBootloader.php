<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Cycle\Annotated;
use Cycle\Annotated\Locator\EmbeddingLocatorInterface;
use Cycle\Annotated\Locator\EntityLocatorInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Attributes\AttributesBootloader;
use Spiral\Core\FactoryInterface;
use Spiral\Cycle\Annotated\Locator\ListenerEmbeddingsLocator;
use Spiral\Cycle\Annotated\Locator\ListenerEntityLocator;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Cycle\Schema\Provider\AnnotatedSchemaProvider;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;

final class AnnotatedBootloader extends Bootloader
{
    public function defineDependencies(): array
    {
        return [
            SchemaBootloader::class,
            TokenizerListenerBootloader::class,
            AttributesBootloader::class,
        ];
    }

    public function defineSingletons(): array
    {
        return [
            ListenerEntityLocator::class => ListenerEntityLocator::class,
            ListenerEmbeddingsLocator::class => ListenerEmbeddingsLocator::class,
            EmbeddingLocatorInterface::class => ListenerEmbeddingsLocator::class,
            EntityLocatorInterface::class => ListenerEntityLocator::class,
            AnnotatedSchemaProvider::class => static function (FactoryInterface $factory, SchemaBootloader $schema, CycleConfig $config) {
                return $factory->make(AnnotatedSchemaProvider::class, ['generators' => $schema->getGenerators($config)]);
            },
        ];
    }

    public function init(
        TokenizerListenerBootloader $tokenizer,
        ListenerEntityLocator $entityLocator,
        ListenerEmbeddingsLocator $embeddingsLocator
    ): void {
        $tokenizer->addListener($entityLocator);
        $tokenizer->addListener($embeddingsLocator);
    }

    /**
     * @deprecated since v2.10.0. Will be removed in v3.0.0.
     */
    public function initEntities(ReaderInterface $reader, ListenerEntityLocator $entityLocator): Annotated\Entities
    {
        return new Annotated\Entities($entityLocator, $reader);
    }

    /**
     * @deprecated since v2.10.0. Will be removed in v3.0.0.
     */
    public function initMergeColumns(ReaderInterface $reader): Annotated\MergeColumns
    {
        return new Annotated\MergeColumns($reader);
    }

    /**
     * @deprecated since v2.10.0. Will be removed in v3.0.0.
     */
    public function initTableInheritance(ReaderInterface $reader): Annotated\TableInheritance
    {
        return new Annotated\TableInheritance($reader);
    }

    /**
     * @deprecated since v2.10.0. Will be removed in v3.0.0.
     */
    public function initMergeIndexes(ReaderInterface $reader): Annotated\MergeIndexes
    {
        return new Annotated\MergeIndexes($reader);
    }
}
