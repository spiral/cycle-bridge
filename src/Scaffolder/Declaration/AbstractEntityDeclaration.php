<?php

declare(strict_types=1);

namespace Spiral\Cycle\Scaffolder\Declaration;

use Doctrine\Inflector\Rules\English\InflectorFactory;
use Spiral\Reactor\Partial\Property;
use Spiral\Reactor\Partial\Visibility;
use Spiral\Scaffolder\Declaration\AbstractDeclaration;

abstract class AbstractEntityDeclaration extends AbstractDeclaration
{
    protected ?string $role = null;
    protected ?string $mapper = null;
    protected ?string $repository = null;
    protected ?string $table = null;
    protected ?string $database = null;
    protected ?string $inflection = null;

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function setMapper(string $mapper): void
    {
        $this->mapper = $mapper;
    }

    public function setRepository(string $repository): void
    {
        $this->repository = $repository;
    }

    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    public function setDatabase(string $database): void
    {
        $this->database = $database;
    }

    public function setInflection(string $inflection): void
    {
        $this->inflection = $inflection;
    }

    /**
     * Add field.
     */
    public function addField(string $name, string $accessibility, string $type): Property
    {
        $property = $this->class
            ->addProperty($name)
            ->setVisibility(Visibility::from($accessibility))
            ->setType($this->variableType($type));

        if ($this->isNullableType($type)) {
            $property->setNullable();
            $property->setValue(null);
        }

        if (!$property->isPublic()) {
            $this->declareAccessors($name, $type);
        }

        return $property;
    }

    abstract public function declareSchema(): void;

    protected function isNullableType(string $type): bool
    {
        return \str_starts_with($type, '?');
    }

    private function variableType(string $type): string
    {
        if ($this->isNullableType($type)) {
            $type = \substr($type, 1);
        }

        $phpMapping = [
            'int'   => [
                'primary',
                'incremental',
                'bigPrimary',
                'int',
                'integer',
                'tinyInteger',
                'smallint',
                'smallInteger',
                'bigint',
                'bigInteger',
                'bigIncremental'
            ],
            'bool'  => ['boolean', 'bool'],
            'float' => ['double', 'float', 'decimal'],
            \DateTimeImmutable::class => ['datetime', 'date', 'time', 'timestamp']
        ];

        foreach ($phpMapping as $phpType => $candidates) {
            if (\in_array($type, $candidates, true)) {
                return $phpType;
            }
        }

        return 'string';
    }

    private function declareAccessors(string $field, string $type): void
    {
        $setter = $this->class->addMethod('set' . $this->classify($field));
        $setter->setPublic();
        $setter->addParameter('value')->setType($type);
        $setter->addBody("\$this->$field = \$value;");

        $getter = $this->class->addMethod('get' . $this->classify($field));
        $getter->setPublic();
        $getter->addBody("return \$this->$field;");
    }

    private function classify(string $name): string
    {
        return (new InflectorFactory())->build()->classify($name);
    }
}
