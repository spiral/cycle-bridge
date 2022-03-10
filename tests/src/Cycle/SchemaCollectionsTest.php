<?php

declare(strict_types=1);

namespace Spiral\Tests\Cycle;

use Cycle\ORM\Collection\ArrayCollectionFactory;
use Cycle\ORM\Collection\DoctrineCollectionFactory;
use Cycle\ORM\Collection\IlluminateCollectionFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Illuminate\Support\Collection;
use Spiral\App\Entities\User;
use Spiral\Tests\BaseTest;
use Spiral\Tests\ConfigAttribute;

final class SchemaCollectionsTest extends BaseTest
{
    protected function setUp(): void
    {
        $this->updateConfig(
            'cycle.schema.collections',
            [
                'default' => 'array',
                'factories' => [
                    'array' => new ArrayCollectionFactory(),
                    'doctrine' => new DoctrineCollectionFactory(),
                    'illuminate' => new IlluminateCollectionFactory(),
                ],
            ],
        );
        parent::setUp();
    }

    #[ConfigAttribute(path: 'cycle.schema.collections.default', value: null)]
    public function testWhenCollectionsConfigIsNotSetArrayShouldBeUsed(): void
    {
        $user = $this->getUser();

        $this->assertCount(1, $user->friends);
        $this->assertIsArray($user->friends);
    }

    #[ConfigAttribute(path: 'cycle.schema.collections.default', value: null)]
    public function testWhenDefaultCollectionIsNotSetArrayShouldBeUsed(): void
    {
        $this->updateConfig('cycle.schema.collections.default', null);
        $user = $this->getUser();

        $this->assertCount(1, $user->friends);
        $this->assertIsArray($user->friends);
    }

    #[ConfigAttribute(path: 'cycle.schema.collections.default', value: 'array')]
    public function testDefaultArrayType(): void
    {
        $user = $this->getUser();

        $this->assertCount(1, $user->friends);
        $this->assertIsArray($user->friends);
    }

    #[ConfigAttribute(path: 'cycle.schema.collections.default', value: 'doctrine')]
    public function testDefaultDoctrineCollectionType(): void
    {
        $user = $this->getUser();

        $this->assertCount(1, $user->friends);
        $this->assertInstanceOf(ArrayCollection::class, $user->friends);
    }

    #[ConfigAttribute(path: 'cycle.schema.collections.default', value: 'illuminate')]
    public function testDefaultIlluminateCollectionType(): void
    {
        $user = $this->getUser();

        $this->assertCount(1, $user->friends);
        $this->assertInstanceOf(Collection::class, $user->friends);
    }

    #[ConfigAttribute(path: 'cycle.schema.collections.default', value: 'array')]
    public function testCollectionShouldUseCorrectType(): void
    {
        $user = $this->getUser();

        $this->assertCount(1, $user->friendsAsArray);
        $this->assertIsArray($user->friendsAsArray);

        $this->assertCount(1, $user->friendsAsDoctrineCollection);
        $this->assertInstanceOf(ArrayCollection::class, $user->friendsAsDoctrineCollection);

        $this->assertCount(1, $user->friendsAsIlluminateCollection);
        $this->assertInstanceOf(Collection::class, $user->friendsAsIlluminateCollection);
    }

    private function getUser(): User
    {
        $em = $this->getEntityManager();

        $friend = new User('Antony');
        $user = new User('John Smith');
        $user->addFriend($friend);

        $em->persist($user);
        $em->run();
        $this->getOrm()->getHeap()->clean();

        return $this->getRepository(User::class)->findByPK(1);
    }
}
