<?php

declare(strict_types=1);

namespace Spiral\Cycle\Scaffolder\Declaration\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Doctrine\Inflector\Rules\English\InflectorFactory;
use Spiral\Reactor\Partial\Property;
use Spiral\Cycle\Scaffolder\Declaration\AbstractEntityDeclaration;
use Spiral\Scaffolder\Exception\ScaffolderException;

class AnnotatedDeclaration extends AbstractEntityDeclaration
{
    public const TYPE = 'entity';

    public function addField(string $name, string $accessibility, string $type): Property
    {
        $property = parent::addField($name, $accessibility, $type);
        $property->addAttribute(Column::class, $this->makeFieldAttribute($name, $type));

        return $property;
    }

    public function declareSchema(): void
    {
        $entities = [];
        $attributes = ['role', 'mapper', 'repository', 'table', 'database'];
        foreach ($attributes as $attribute) {
            if (!empty($this->$attribute)) {
                $entities[$attribute] = $this->$attribute;
            }
        }

        $this->class->addAttribute(Entity::class, $entities);
    }

    private function makeFieldAttribute(string $name, string $type): array
    {
        $columns = [];
        if ($this->isNullableType($type)) {
            $columns['nullable'] = true;
        }
        $columns['type'] = $this->annotatedType($type);

        if (!empty($this->inflection)) {
            $columns = $this->addInflectedName($this->inflection, $name, $columns);
        }

        return $columns;
    }

    private function annotatedType(string $type): string
    {
        return $this->isNullableType($type) ? \substr($type, 1) : $type;
    }

    private function addInflectedName(string $inflection, string $name, array $columns): array
    {
        $inflected = $this->inflect($inflection, $name);
        if ($inflected !== null && $inflected !== $name) {
            $columns['name'] = $inflected;
        }

        return $columns;
    }

    private function inflect(string $inflection, string $value): ?string
    {
        return match ($inflection) {
            'tableize', 't' => $this->tableize($value),
            'camelize', 'c' => $this->camelize($value),
            default => throw new ScaffolderException("Unknown inflection, got `$inflection`"),
        };
    }

    private function tableize(string $name): string
    {
        return (new InflectorFactory())->build()->tableize($name);
    }

    private function camelize(string $name): string
    {
        return (new InflectorFactory())->build()->camelize($name);
    }

    public function declare(): void
    {
        $this->namespace->addUse(Column::class);
        $this->namespace->addUse(Entity::class);
    }
}
