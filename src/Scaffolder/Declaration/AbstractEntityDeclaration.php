<?php

declare(strict_types=1);

namespace Spiral\Cycle\Scaffolder\Declaration;

use Doctrine\Inflector\Rules\English\InflectorFactory;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\DependedInterface;
use Spiral\Reactor\Partial\Property;

abstract class AbstractEntityDeclaration extends ClassDeclaration implements DependedInterface
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
        $property = $this->property($name);
        $property->setComment("@var {$this->variableType($type)}");
        if ($accessibility) {
            $property->setAccess($accessibility);
        }

        if ($property->getAccess() !== self::ACCESS_PUBLIC) {
            $this->declareAccessors($name, $type);
        }

        return $property;
    }

    abstract public function declareSchema(): void;

    protected function isNullableType(string $type): bool
    {
        return str_starts_with($type, '?');
    }

    private function variableType(string $type): string
    {
        return $this->isNullableType($type) ? (substr($type, 1) . '|null') : $type;
    }

    private function declareAccessors(string $field, string $type): void
    {
        $setter = $this->method('set' . $this->classify($field));
        $setter->setPublic();
        $setter->parameter('value')->setType($type);
        $setter->setSource("\$this->$field = \$value;");

        $getter = $this->method('get' . $this->classify($field));
        $getter->setPublic();
        $getter->setSource("return \$this->$field;");
    }

    private function classify(string $name): string
    {
        return (new InflectorFactory())->build()->classify($name);
    }
}
