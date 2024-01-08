<?php

declare(strict_types=1);

namespace Spiral\App\Database\Factory;

use Spiral\App\Entities\Role;
use Spiral\DatabaseSeeder\Factory\AbstractFactory;
use Spiral\DatabaseSeeder\Factory\FactoryInterface;

/**
 * @implements FactoryInterface<Role>
 */
final class RoleFactory extends AbstractFactory
{
    public function makeEntity(array $definition): object
    {
        return new Role($definition['name']);
    }

    public function entity(): string
    {
        return Role::class;
    }

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
        ];
    }
}
