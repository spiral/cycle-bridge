<?php

declare(strict_types=1);

namespace Spiral\Cycle\Validation;

use Cycle\Database\Injection\Expression;
use Cycle\Database\Injection\Parameter;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Select\Repository;
use RuntimeException;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Validator\AbstractChecker;

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
    public function exists(
        mixed $value,
        string $role,
        ?string $field = null,
        bool $ignoreCase = false,
        bool $multiple = false
    ): bool {
        $repository = $this->orm->getRepository($role);
        $pk = (array)$this->orm->getSchema()->define($role, SchemaInterface::PRIMARY_KEY);
        $isComposite = \count($pk) > 1;
        $isPK = $field === null || (!$isComposite && $pk[0] === $field);

        if ($isPK) {
            if (!$multiple) {
                return $repository->findByPK($value) !== null;
            }
            if ($repository instanceof Repository) {
                return $repository->select()->wherePK(...(array) $value)->count() === \count($value);
            }
            throw new RuntimeException(
                \sprintf('The `%s` repository doesn\'t support the multiple validation.', $repository::class)
            );
        }
        \assert($field !== null);

        if (!$ignoreCase) {
            if (!$multiple) {
                return $repository->findOne([$field => $value]) !== null;
            }
            if ($repository instanceof Repository) {
                return $repository->select()
                    ->where($field, 'IN', new Parameter((array) $value))
                    ->count() === \count($value);
            }
            throw new RuntimeException(\sprintf(
                'The `%s` repository doesn\'t support the multiple validation by custom field.',
                $repository::class
            ));
        }

        if ($repository instanceof Repository) {
            return $this->whereCaseInsensitive($repository->select(), $field, $value, $multiple)->fetchOne() !== null;
        }

        throw new RuntimeException(\sprintf(
            'The `%s` repository doesn\'t support the case insensitive validation by custom field.',
            $repository::class
        ));
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
                $this->whereCaseInsensitive($select, $key, $fieldValue, false);
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

    private function whereCaseInsensitive(Select $select, string $field, mixed $value, bool $multiple): Select
    {
        $queryBuilder = $select->getBuilder();
        $column = new Expression("LOWER({$queryBuilder->resolve($field)})");

        if (!$multiple) {
            return $select->where($column, \is_string($value) ? \mb_strtolower($value) : $value);
        }

        throw new RuntimeException(
            'The `exists` rule doesn\'t work in multiple case insensitive mode.',
        );
    }
}
