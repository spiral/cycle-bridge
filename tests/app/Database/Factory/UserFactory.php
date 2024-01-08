<?php

declare(strict_types=1);

namespace Spiral\App\Database\Factory;

use Spiral\App\Entities\Role;
use Spiral\App\Entities\User;
use Spiral\DatabaseSeeder\Factory\AbstractFactory;
use Spiral\DatabaseSeeder\Factory\FactoryInterface;

/**
 * @implements FactoryInterface<User>
 */
final class UserFactory extends AbstractFactory
{
    public function makeEntity(array $definition): object
    {
        $user = new User($definition['name']);
        $user->email = $definition['email'];
        $user->company = $definition['company'];

        return $user;
    }

    public function addRole(Role $role): self
    {
        return $this->entityState(static function (User $user) use ($role) {
            $user->roles->add($role);

            return $user;
        });
    }

    public function entity(): string
    {
        return User::class;
    }

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'company' => $this->faker->company,
        ];
    }
}
