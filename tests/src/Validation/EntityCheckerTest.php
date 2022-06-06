<?php

declare(strict_types=1);

namespace Spiral\Tests\Validation;

use Cycle\Database\DatabaseInterface;
use Spiral\App\Entities\User;
use Spiral\Tests\BaseTest;
use Spiral\Validation\ValidationProviderInterface;
use Spiral\Validator\FilterDefinition;

final class EntityCheckerTest extends BaseTest
{
    private const ENTITY_PK = 'id';

    private DatabaseInterface $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->getContainer()->get(DatabaseInterface::class);

        $users = $this->db->table('users')->getSchema();
        $users->primary('id');
        $users->string('name');
        $users->string('email');
        $users->string('company');
        $users->integer('user_id');
        $users->save();

        $this->db->table('users')->insertMultiple(['id', 'name', 'email', 'company'], [
            [1, 'Antony', 'test@mail.com', 'foo'],
            [2, 'John', 'test2@mail.com', 'bar'],
        ]);
    }

    /**
     * @see \Spiral\Cycle\Validation\EntityChecker::exists()
     */
    public function existsDataProvider(): iterable
    {
        return [
            'pk found' => [
                'rules' => [self::ENTITY_PK => [['entity::exists', User::class]]],
                'entityData' => [self::ENTITY_PK => 1, 'email' => 'test@mail.com'],
                'valid' => true,
            ],
            'pk not found' => [
                'rules' => [self::ENTITY_PK => [['entity::exists', User::class]]],
                'entityData' => [self::ENTITY_PK => 3, 'email' => 'test@mail.com'],
                'valid' => false,
            ],
            'custom field - found' => [
                'rules' => ['email' => [['entity::exists', User::class, 'email']]],
                'entityData' => [self::ENTITY_PK => 1, 'email' => 'test@mail.com'],
                'valid' => true,
            ],
            'custom field - not found' => [
                'rules' => ['email' => [['entity::exists', User::class, 'email']]],
                'entityData' => [self::ENTITY_PK => 2, 'email' => 'undefined@mail.com'],
                'valid' => false,
            ],
            'multiple pk found' => [
                'rules' => [self::ENTITY_PK => [['entity::exists', User::class, 'multiple' => true]]],
                'entityData' => [
                    self::ENTITY_PK => [1, 2],
                    'email' => [
                        'test@mail.com',
                        'test2@mail.com',
                    ]
                ],
                'valid' => true,
            ],
            'multiple pk not found' => [
                'rules' => [self::ENTITY_PK => [['entity::exists', User::class, 'multiple' => true]]],
                'entityData' => [
                    self::ENTITY_PK => [42, 1],
                    'email' => [
                        'test@mail.com',
                        'test2@mail.com',
                    ]
                ],
                'valid' => false,
            ],
            'multiple custom field - found' => [
                'rules' => ['email' => [['entity::exists', User::class, 'email', 'multiple' => true]]],
                'entityData' => [
                    self::ENTITY_PK => [42, 69],
                    'email' => [
                        'test@mail.com',
                        'test2@mail.com',
                    ]
                ],
                'valid' => true,
            ],
            'multiple custom field - not found' => [
                'rules' => ['email' => [['entity::exists', User::class, 'email', 'multiple' => true]]],
                'entityData' => [
                    self::ENTITY_PK => [42, 69],
                    'email' => [
                        'test@mail.com',
                        'not-exist@mail.com',
                    ]
                ],
                'valid' => false,
            ],
            'ignore case false - found' => [
                'rules' => ['email' => [['entity::exists', User::class, 'email']]],
                'entityData' => [self::ENTITY_PK => 1, 'email' => 'test@mail.com'],
                'valid' => true,
            ],
            'ignore case false - not found' => [
                'rules' => ['email' => [['entity::exists', User::class, 'email']]],
                'entityData' => [self::ENTITY_PK => 2, 'email' => 'TEST@mail.com'],
                'valid' => false,
            ],
            'ignore case true - found' => [
                'rules' => ['email' => [['entity::exists', User::class, 'email', 'ignoreCase' => true]]],
                'entityData' => [self::ENTITY_PK => 2, 'email' => 'TEST@mail.com'],
                'valid' => true,
            ],
        ];
    }

    /**
     * @see \Spiral\Cycle\Validation\EntityChecker::unique()
     */
    public function uniqueDataProvider(): iterable
    {
        return [
            'unique valid simple' => [
                'rules' => ['email' => [['entity::unique', User::class, 'email']]],
                'entityData' => [self::ENTITY_PK => 42, 'email' => 'unique@mail.com', 'company' => 'bar'],
                'valid' => true,
            ],
            'unique valid' => [
                'rules' => ['email' => [['entity::unique', User::class, 'email', ['company']]]],
                'entityData' => [self::ENTITY_PK => 42, 'email' => 'test@mail.com', 'company' => 'baz'],
                'valid' => true,
            ],
            'unique invalid' => [
                'rules' => ['email' => [['entity::unique', User::class, 'email', ['company']]]],
                'entityData' => [self::ENTITY_PK => 42, 'email' => 'test@mail.com', 'company' => 'foo'],
                'valid' => false,
            ],
            'unique ignore case invalid' => [
                'rules' => ['email' => [['entity::unique', User::class, 'email', ['company'], 'ignoreCase' => true]]],
                'entityData' => [self::ENTITY_PK => 42, 'email' => 'test@mail.com', 'company' => 'FOO'],
                'valid' => false,
            ],
            'unique ignore case valid' => [
                'rules' => ['email' => [['entity::unique', User::class, 'email', ['company'], 'ignoreCase' => false]]],
                'entityData' => [self::ENTITY_PK => 42, 'email' => 'test@mail.com', 'company' => 'FOO'],
                'valid' => true,
            ],
        ];
    }

    public function mainDataProvider(): iterable
    {
        yield from $this->existsDataProvider();
        yield from $this->uniqueDataProvider();
    }

    /**
     * @dataProvider mainDataProvider
     */
    public function testExistsAndUnique(
        array $rules,
        array $entityData,
        bool $valid
    ): void {
        $provider = $this->getContainer()->get(ValidationProviderInterface::class);
        $validator = $provider->getValidation(FilterDefinition::class)->validate($entityData, $rules);

        $this->assertSame($valid, $validator->isValid());
    }

    public function badCasesProvider(): iterable
    {
        return [
            'pk ignore case true multiple - found' => [
                'rules' => [
                    'email' => [['entity::exists', User::class, 'email', 'ignoreCase' => true, 'multiple' => true]]
                ],
                'entityData' => [strtoupper(self::ENTITY_PK) => [1, 2], 'email' => 'TEST@mail.com'],
                'exceptionText' => 'The `exists` rule doesn\'t work in multiple case insensitive mode.',
            ],
            'pk ignore case true multiple - not found' => [
                'rules' => [
                    'email' => [['entity::exists', User::class, 'email', 'ignoreCase' => true, 'multiple' => true]]
                ],
                'entityData' => [strtoupper(self::ENTITY_PK) => [2, 96], 'email' => 'TEST@mail.com'],
                'exceptionText' => 'The `exists` rule doesn\'t work in multiple case insensitive mode.',
            ],
        ];
    }

    /**
     * @dataProvider badCasesProvider
     */
    public function testExistsAndUniqueExceptions(
        array $rules,
        array $entityData,
        string $exceptionText
    ): void {
        $provider = $this->getContainer()->get(ValidationProviderInterface::class);
        $validator = $provider->getValidation(FilterDefinition::class)->validate($entityData, $rules);

        $this->expectExceptionMessage($exceptionText);
        $validator->isValid();
    }
}
