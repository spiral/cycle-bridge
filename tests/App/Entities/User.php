<?php

declare(strict_types=1);

namespace Spiral\App\Entities;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Cycle\Annotated\Annotation\Relation\HasMany;
use Doctrine\Common\Collections\ArrayCollection;

#[Entity]
class User
{
    #[Column(type: 'primary')]
    private int $id;

    #[Column(type: 'int', name: 'user_id', nullable: true)]
    private ?int $userId = null;

    #[BelongsTo(target: User::class, nullable: true, innerKey: 'userId')]
    public ?User $friend = null;

    #[HasMany(target: User::class, nullable: true, outerKey: 'userId')]
    public iterable $friends = [];

    #[HasMany(target: User::class, nullable: true, outerKey: 'userId', collection: 'array')]
    public array $friendsAsArray = [];

    #[HasMany(target: User::class, nullable: true, outerKey: 'userId', collection: 'doctrine')]
    public \Doctrine\Common\Collections\Collection $friendsAsDoctrineCollection;

    #[HasMany(target: User::class, nullable: true, outerKey: 'userId', collection: 'illuminate')]
    public \Illuminate\Support\Collection $friendsAsIlluminateCollection;

    public function __construct(
        #[Column(type: 'string')]
        private string $name
    ) {
        $this->friendsAsDoctrineCollection = new ArrayCollection();
        $this->friendsAsIlluminateCollection = new \Illuminate\Support\Collection();
    }

    public function addFriend(User $user): void
    {
        $this->friendsAsArray[] = $user;
        $user->friend = $this;
    }
}
