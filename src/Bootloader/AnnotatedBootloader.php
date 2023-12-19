<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Cycle\Annotated;
use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Attributes\AttributesBootloader;
use Spiral\Cycle\Annotated\Locator\ListenerEmbeddingsLocator;
use Spiral\Cycle\Annotated\Locator\ListenerEntityLocator;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;

final class AnnotatedBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        SchemaBootloader::class,
        TokenizerListenerBootloader::class,
        AttributesBootloader::class,
    ];

    protected const BINDINGS = [
        Annotated\Embeddings::class => [self::class, 'initEmbeddings'],
        Annotated\Entities::class => [self::class, 'initEntities'],
        Annotated\MergeColumns::class => [self::class, 'initMergeColumns'],
        Annotated\TableInheritance::class => [self::class, 'initTableInheritance'],
        Annotated\MergeIndexes::class => [self::class, 'initMergeIndexes'],
    ];

    protected const SINGLETONS = [
        ListenerEntityLocator::class => ListenerEntityLocator::class,
        ListenerEmbeddingsLocator::class => ListenerEmbeddingsLocator::class,
    ];

    public function init(
        SchemaBootloader $schema,
        TokenizerListenerBootloader $tokenizer,
        ListenerEntityLocator $entityLocator,
        ListenerEmbeddingsLocator $embeddingsLocator
    ): void {
        $tokenizer->addListener($entityLocator);
        $tokenizer->addListener($embeddingsLocator);

        $schema->addGenerator(SchemaBootloader::GROUP_INDEX, Annotated\Embeddings::class);
        $schema->addGenerator(SchemaBootloader::GROUP_INDEX, Annotated\Entities::class);
        $schema->addGenerator(SchemaBootloader::GROUP_INDEX, Annotated\TableInheritance::class);
        $schema->addGenerator(SchemaBootloader::GROUP_INDEX, Annotated\MergeColumns::class);
        $schema->addGenerator(SchemaBootloader::GROUP_RENDER, Annotated\MergeIndexes::class);
    }

    private function initEmbeddings(
        ReaderInterface $reader,
        ListenerEmbeddingsLocator $embeddingsLocator
    ): Annotated\Embeddings {
        return new Annotated\Embeddings($embeddingsLocator, $reader);
    }

    public function initEntities(ReaderInterface $reader, ListenerEntityLocator $entityLocator): Annotated\Entities
    {
        return new Annotated\Entities($entityLocator, $reader);
    }

    public function initMergeColumns(ReaderInterface $reader): Annotated\MergeColumns
    {
        return new Annotated\MergeColumns($reader);
    }

    public function initTableInheritance(ReaderInterface $reader): Annotated\TableInheritance
    {
        return new Annotated\TableInheritance($reader);
    }

    public function initMergeIndexes(ReaderInterface $reader): Annotated\MergeIndexes
    {
        return new Annotated\MergeIndexes($reader);
    }
}
