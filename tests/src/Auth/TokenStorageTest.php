<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth;

use Spiral\Auth\TokenInterface;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Cycle\Auth\TokenStorage;
use Spiral\Tests\BaseTest;

final class TokenStorageTest extends BaseTest
{
    private TokenStorage $storage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = $this->getContainer()->get(TokenStorageInterface::class);
    }

    public function testTokenShouldBeCreatedWithoutExpiration()
    {
        $token = $this->storage->create(['foo' => 'bar']);

        $this->assertInstanceOf(TokenInterface::class, $token);
        $this->assertNotNull($token->getID());
        $this->assertNull($token->getExpiresAt());
        $this->assertSame(['foo' => 'bar'], $token->getPayload());
    }

    public function testTokenShouldBeCreatedWithExpiration()
    {
        $token = $this->storage->create(['foo' => 'bar'], $date = new \DateTimeImmutable('2010-05-05 12:34:56'));

        $this->assertInstanceOf(TokenInterface::class, $token);
        $this->assertNotNull($token->getID());
        $this->assertSame($date, $token->getExpiresAt());
        $this->assertSame(['foo' => 'bar'], $token->getPayload());
    }

    public function testTokenShouldBeLoadedById()
    {
        $token = $this->storage->create(['foo' => 'bar']);

        $this->getOrm()->getHeap()->clean();

        $loadedToken = $this->storage->load($token->getID());

        $this->assertNotSame($loadedToken, $token);
        $this->assertSame($loadedToken->getID(), $token->getID());
        $this->assertSame($loadedToken->getPayload(), $token->getPayload());
        $this->assertSame($loadedToken->getExpiresAt(), $token->getExpiresAt());
    }

    public function testTokenShouldBeDeleted()
    {
        $token = $this->storage->create(['foo' => 'bar']);
        $this->storage->delete($token);

        $this->getOrm()->getHeap()->clean();

        $this->assertNull($this->storage->load($token->getID()));
    }
}
