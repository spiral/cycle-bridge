<?php

declare(strict_types=1);

namespace Spiral\Cycle\Validation;

use Cycle\Database\Injection\Expression;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Select\Repository;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Validation\AbstractChecker;

/**
 * Cycle ORM specific checker
 */
class EntityChecker extends AbstractChecker implements SingletonInterface
{
    /**
     * {@inheritdoc}
     */
    public const MESSAGES = [
        'exists' => '[[Entity not exists.]]',
        'unique' => '[[Value should be unique.]]',
    ];

    public function __construct(
        private ORMInterface $orm
    ) {
    }

    /**
     * Checks if the entity exists by a given field
     *
     * @param string $role Entity class or role
     * @param null|string $field Mapped field
     */
    public function exists(mixed $value, string $role, ?string $field = null, bool $ignoreCase = false): bool
    {
        $repository = $this->orm->getRepository($role);
        if ($field === null) {
            return $repository->findByPK($value) !== null;
        }

        if ($ignoreCase && $repository instanceof Repository) {
            return $this->addCaseInsensitiveWhere($repository->select(), $field, $value)->fetchOne() !== null;
        }

        return $repository->findOne([$field => $value]) !== null;
    }

    /**
     * @param string[] $withFields
     */
    public function unique(
        mixed $value,
        string $role,
        string $field,
        array $withFields = [],
        bool $ignoreCase = false
    ): bool {
        $values = $this->withValues($withFields);
        $values[$field] = $value;

        if ($this->isProvidedByContext($role, $values)) {
            return true;
        }

        $repository = $this->orm->getRepository($role);

        if ($ignoreCase && $repository instanceof Repository) {
            $select = $repository->select();

            foreach ($values as $key => $fieldValue) {
                $this->addCaseInsensitiveWhere($select, $key, $fieldValue);
            }

            return $select->fetchOne() === null;
        }

        return $repository->findOne($values) === null;
    }

    /**
     * @param string[] $fields
     *
     * @return array<string, mixed>
     */
    private function withValues(array $fields): array
    {
        $values = [];
        foreach ($fields as $field) {
            if ($this->getValidator()->hasValue($field)) {
                $values[$field] = $this->getValidator()->getValue($field);
            }
        }

        return $values;
    }

    private function isProvidedByContext(string $role, array $values): bool
    {
        $entity = $this->getValidator()->getContext();
        if (!\is_object($entity) || !$this->orm->getHeap()->has($entity)) {
            return false;
        }

        $extract = $this->orm->getMapper($role)->extract($entity);
        foreach ($values as $field => $value) {
            if (!isset($extract[$field]) || $extract[$field] !== $value) {
                return false;
            }
        }

        return true;
    }

    private function addCaseInsensitiveWhere(Select $select, string $field, mixed $value): Select
    {
        if (!is_string($value)) {
            return $select->where($field, $value);
        }

        $queryBuilder = $select->getBuilder();

        return $select
            ->where(
                new Expression("LOWER({$queryBuilder->resolve($field)})"),
                mb_strtolower($value)
            );
    }
}
