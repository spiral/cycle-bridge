<?php

declare(strict_types=1);

namespace Spiral\Tests\Cycle;

use Cycle\ORM\Collection\ArrayCollectionFactory;
use Cycle\ORM\Collection\DoctrineCollectionFactory;
use Cycle\ORM\Collection\IlluminateCollectionFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Illuminate\Support\Collection;
use Spiral\App\Entities\User;
use Spiral\Tests\TestCase;

final class SchemaCollectionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->updateConfig('cycle.schema.collections', [
            'default' => 'array',
            'factories' => [
                'array' => new ArrayCollectionFactory(),
                'doctrine' => new DoctrineCollectionFactory(),
                'illuminate' => new IlluminateCollectionFactory(),
            ],
        ]);
    }

    public function testWhenCollectionsConfigIsNotSetArrayShouldBeUsed(): void
    {
        $this->updateConfig('cycle.schema.collections', null);
        $user = $this->getUser();

        $this->assertCount(1, $user->friends);
        $this->assertIsArray($user->friends);
    }

    public function testWhenDefaultCollectionIsNotSetArrayShouldBeUsed(): void
    {
        $this->updateConfig('cycle.schema.collections.default', null);
        $user = $this->getUser();

        $this->assertCount(1, $user->friends);
        $this->assertIsArray($user->friends);
    }

    public function testDefaultArrayType(): void
    {
        $this->updateConfig('cycle.schema.collections.default', 'array');
        $user = $this->getUser();

        $this->assertCount(1, $user->friends);
        $this->assertIsArray($user->friends);
    }

    public function testDefaultDoctrineCollectionType(): void
    {
        $this->updateConfig('cycle.schema.collections.default', 'doctrine');
        $user = $this->getUser();

        $this->assertCount(1, $user->friends);
        $this->assertInstanceOf(ArrayCollection::class, $user->friends);
    }

    public function testDefaultIlluminateCollectionType(): void
    {
        $this->updateConfig('cycle.schema.collections.default', 'illuminate');
        $user = $this->getUser();

        $this->assertCount(1, $user->friends);
        $this->assertInstanceOf(Collection::class, $user->friends);
    }

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
