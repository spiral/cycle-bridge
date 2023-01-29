<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Cycle\Annotated;
use Spiral\Attributes\Factory;
use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;
use Spiral\Tokenizer\ClassesInterface;

final class AnnotatedBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        SchemaBootloader::class,
        TokenizerBootloader::class,
    ];

    protected const BINDINGS = [
        ReaderInterface::class => [self::class, 'initReader'],
        Annotated\Embeddings::class => [self::class, 'initEmbeddings'],
        Annotated\Entities::class => [self::class, 'initEntities'],
        Annotated\MergeColumns::class => [self::class, 'initMergeColumns'],
        Annotated\TableInheritance::class => [self::class, 'initTableInheritance'],
        Annotated\MergeIndexes::class => [self::class, 'initMergeIndexes'],
    ];

    public function init(SchemaBootloader $schema): void
    {
        $schema->addGenerator(SchemaBootloader::GROUP_INDEX, Annotated\Embeddings::class);
        $schema->addGenerator(SchemaBootloader::GROUP_INDEX, Annotated\Entities::class);
        $schema->addGenerator(SchemaBootloader::GROUP_INDEX, Annotated\TableInheritance::class);
        $schema->addGenerator(SchemaBootloader::GROUP_INDEX, Annotated\MergeColumns::class);
        $schema->addGenerator(SchemaBootloader::GROUP_RENDER, Annotated\MergeIndexes::class);
    }

    private function initReader(): ReaderInterface
    {
        return (new Factory)->create();
    }

    private function initEmbeddings(ClassesInterface $classes, ReaderInterface $reader): Annotated\Embeddings
    {
        return new Annotated\Embeddings($classes, $reader);
    }

    public function initEntities(ClassesInterface $classes, ReaderInterface $reader): Annotated\Entities
    {
        return new Annotated\Entities($classes, $reader);
    }

    public function initMergeColumns(ClassesInterface $classes, ReaderInterface $reader): Annotated\MergeColumns
    {
        return new Annotated\MergeColumns($reader);
    }

    public function initTableInheritance(ClassesInterface $classes, ReaderInterface $reader): Annotated\TableInheritance
    {
        return new Annotated\TableInheritance($reader);
    }

    public function initMergeIndexes(ClassesInterface $classes, ReaderInterface $reader): Annotated\MergeIndexes
    {
        return new Annotated\MergeIndexes($reader);
    }
}

