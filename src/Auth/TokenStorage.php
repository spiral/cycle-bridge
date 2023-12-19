<?php

declare(strict_types=1);

namespace Spiral\Cycle\Auth;

use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Spiral\Auth\Exception\TokenStorageException;
use Spiral\Auth\TokenInterface;
use Spiral\Auth\TokenStorageInterface;
use Throwable;

/**
 * Provides the ability to fetch token information from the database via Cycle ORM.
 */
final class TokenStorage implements TokenStorageInterface
{
    public function __construct(
        private readonly ORMInterface $orm,
        private readonly EntityManagerInterface $em
    ) {
    }

    public function load(string $id): ?TokenInterface
    {
        if (!\str_contains($id, ':')) {
            return null;
        }

        [$pk, $hash] = \explode(':', $id, 2);

        /** @var Token $token */
        $token = $this->orm->getRepository(Token::class)->findByPK($pk);

        if ($token === null || !\hash_equals($token->getHashedValue(), \hash('sha512', $hash))) {
            // hijacked or deleted
            return null;
        }

        $token->setSecretValue($hash);

        $expiresAt = $token->getExpiresAt();
        if ($expiresAt !== null && $expiresAt < new DateTimeImmutable()) {
            $this->delete($token);

            return null;
        }

        return $token;
    }

    public function create(array $payload, DateTimeInterface $expiresAt = null): TokenInterface
    {
        try {
            $token = new Token(
                $this->issueID(),
                $this->randomHash(128),
                $payload,
                new DateTimeImmutable(),
                $expiresAt
            );

            $this->em->persist($token);
            $this->em->run();

            return $token;
        } catch (Throwable $e) {
            throw new TokenStorageException('Unable to create token', (int)$e->getCode(), $e);
        }
    }

    public function delete(TokenInterface $token): void
    {
        try {
            $this->em->delete($token);
            $this->em->run();
        } catch (Throwable $e) {
            throw new TokenStorageException('Unable to delete token', (int)$e->getCode(), $e);
        }
    }

    /**
     * Issue unique token id.
     */
    private function issueID(): string
    {
        $id = $this->randomHash(64);

        /** @psalm-suppress InternalMethod */
        $query = $this->orm->getSource(Token::class)
            ->getDatabase()
            ->select()
            ->from($this->orm->getSource(Token::class)->getTable());

        /** @psalm-suppress InternalMethod */
        while ((clone $query)->where('id', $id)->count('id') !== 0) {
            $id = $this->randomHash(64);
        }

        return $id;
    }

    private function randomHash(int $length): string
    {
        return \substr(\bin2hex(random_bytes($length)), 0, $length);
    }
}
