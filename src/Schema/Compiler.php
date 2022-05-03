<?php

declare(strict_types=1);

namespace Spiral\Cycle\Schema;

use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Cycle\Schema\Registry;
use Spiral\Boot\MemoryInterface;

final class Compiler
{
    private const MEMORY_SECTION = 'cycle';
    private const EMPTY_SCHEMA = ':empty:';

    private mixed $schema = null;

    private function __construct(mixed $schema)
    {
        $this->schema = $schema;
    }

    public static function fromMemory(MemoryInterface $memory): self
    {
        return new self($memory->loadData(self::MEMORY_SECTION));
    }

    public static function compile(Registry $registry, array $generators, array $defaults = []): self
    {
        return new self((new \Cycle\Schema\Compiler())->compile($registry, $generators, $defaults));
    }

    public function isEmpty(): bool
    {
        return empty($this->schema) || $this->schema === self::EMPTY_SCHEMA;
    }

    public function toSchema(): SchemaInterface
    {
        return new Schema($this->isWriteableSchema() ? $this->schema : []);
    }

    public function toMemory(MemoryInterface $memory)
    {
        return $memory->saveData(
            self::MEMORY_SECTION,
            empty($this->schema) ? self::EMPTY_SCHEMA : $this->schema
        );
    }

    private function isWriteableSchema(): bool
    {
        return \is_array($this->schema);
    }
}
