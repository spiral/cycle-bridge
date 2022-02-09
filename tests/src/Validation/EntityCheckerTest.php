<?php

declare(strict_types=1);

namespace Spiral\Tests\Validation;

use Cycle\ORM\Heap\Heap;
use Cycle\ORM\ORMInterface;
use Mockery as m;
use Spiral\Tests\BaseTest;
use Spiral\Validation\ValidationInterface;

// use Spiral\Validation\ValidatorInterface;

final class EntityCheckerTest extends BaseTest
{
    use EntityCheckerTrait;

    private const ENTITY_ROLE = 'testRole';
    private const ENTITY_PK = 'id';

    /**
     * @see \Spiral\Cycle\Validation\EntityChecker::exists()
     */
    public function existsDataProvider(): iterable
    {
        $repoData = [
            [self::ENTITY_PK => 42, 'email' => 'test@mail.com', 'foo' => 'bar', 'value' => 'test value'],
            [self::ENTITY_PK => 69, 'email' => 'test2@mail.com', 'company' => 'foo', 'value' => 'test value 2'],
        ];
        return [
            'pk found' => [
                'rules' => [self::ENTITY_PK => [['entity::exists', self::ENTITY_ROLE]]],
                'repositoryData' => $repoData,
                'entityData' => [self::ENTITY_PK => 42, 'email' => 'test@mail.com'],
                'valid' => true,
            ],
            'pk not found' => [
                'rules' => [self::ENTITY_PK => [['entity::exists', self::ENTITY_ROLE]]],
                'repositoryData' => $repoData,
                'entityData' => [self::ENTITY_PK => 1, 'email' => 'test@mail.com'],
                'valid' => false,
            ],
            'custom field - found' => [
                'rules' => ['email' => [['entity::exists', self::ENTITY_ROLE, 'email']]],
                'repositoryData' => $repoData,
                'entityData' => [self::ENTITY_PK => 1, 'email' => 'test@mail.com'],
                'valid' => true,
            ],
            'custom field - not found' => [
                'rules' => ['email' => [['entity::exists', self::ENTITY_ROLE, 'email']]],
                'repositoryData' => $repoData,
                'entityData' => [self::ENTITY_PK => 42, 'email' => 'undefined@mail.com'],
                'valid' => false,
            ],
            'array pk found' => [
                'rules' => [self::ENTITY_PK => [['entity::exists', self::ENTITY_ROLE]]],
                'repositoryData' => $repoData,
                'entityData' => [
                    self::ENTITY_PK => [42, 69],
                    'email' => [
                        'test@mail.com',
                        'test2@mail.com',
                    ]
                ],
                'valid' => true,
            ],
            'array pk not found' => [
                'rules' => [self::ENTITY_PK => [['entity::exists', self::ENTITY_ROLE]]],
                'repositoryData' => $repoData,
                'entityData' => [
                    self::ENTITY_PK => [42, 1],
                    'email' => [
                        'test@mail.com',
                        'test2@mail.com',
                    ]
                ],
                'valid' => false,
            ],
            'array custom field - found' => [
                'rules' => ['email' => [['entity::exists', self::ENTITY_ROLE, 'email']]],
                'repositoryData' => $repoData,
                'entityData' => [
                    self::ENTITY_PK => [42, 69],
                    'email' => [
                        'test@mail.com',
                        'test2@mail.com',
                    ]
                ],
                'valid' => true,
            ],
            'array custom field - not found' => [
                'rules' => ['email' => [['entity::exists', self::ENTITY_ROLE, 'email']]],
                'repositoryData' => $repoData,
                'entityData' => [
                    self::ENTITY_PK => [42, 69],
                    'email' => [
                        'test@mail.com',
                        'not-exist@mail.com',
                    ]
                ],
                'valid' => false,
            ],
        ];
    }

    /**
     * @see \Spiral\Cycle\Validation\EntityChecker::unique()
     */
    public function uniqueDataProvider(): iterable
    {
        $repoData = [
            [self::ENTITY_PK => 42, 'email' => 'test@mail.com', 'company' => 'bar', 'value' => 'test value 1'],
            [self::ENTITY_PK => 69, 'email' => 'test@mail.com', 'company' => 'foo', 'value' => 'test value 2'],
        ];
        return [
            'unique valid simple' => [
                'rules' => ['email' => [['entity::unique', self::ENTITY_ROLE, 'email']]],
                'repositoryData' => $repoData,
                'entityData' => [self::ENTITY_PK => 42, 'email' => 'unique@mail.com', 'company' => 'bar'],
                'valid' => true,
            ],
            'unique valid' => [
                'rules' => ['email' => [['entity::unique', self::ENTITY_ROLE, 'email', ['company']]]],
                'repositoryData' => $repoData,
                'entityData' => [self::ENTITY_PK => 42, 'email' => 'test@mail.com', 'company' => 'baz'],
                'valid' => true,
            ],
            'unique invalid' => [
                'rules' => ['email' => [['entity::unique', self::ENTITY_ROLE, 'email', ['company']]]],
                'repositoryData' => $repoData,
                'entityData' => [self::ENTITY_PK => 42, 'email' => 'test@mail.com', 'company' => 'foo'],
                'valid' => false,
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
    public function testExistsWithEmptyDatabase(
        array $rules,
        array $repositoryData,
        array $entityData,
        bool $valid
    ): void {
        $this->makeAndBindOrm($repositoryData);
        $validation = $this->app->get(ValidationInterface::class);
        $validator = $validation->validate($entityData, $rules);

        $this->assertSame($valid, $validator->isValid());
    }

    /**
     * @param array<int, non-empty-array<non-empty-string, mixed>> $repositoryData
     */
    private function makeAndBindOrm(array $repositoryData = []): void
    {
        $orm = m::mock(ORMInterface::class);
        $orm->shouldReceive('getRepository')
            ->with(self::ENTITY_ROLE)
            ->andReturn($this->makeRepository($repositoryData, self::ENTITY_PK));
        $orm->shouldReceive('getHeap')
            ->andReturn(new Heap());
        $this->app->getContainer()->bind(ORMInterface::class, $orm);
    }
}