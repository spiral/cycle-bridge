<?php

declare(strict_types=1);

namespace Spiral\Cycle\Auth;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use DateTimeInterface;
use Spiral\Auth\TokenInterface;
use Spiral\Auth\Exception\TokenStorageException;

#[Entity(table: 'auth_tokens')]
class Token implements TokenInterface
{
    #[Column(type: 'string(64)', primary: true)]
    private string $id;

    private string $secretValue;

    #[Column(type: 'string(128)', name: 'hashed_value')]
    private string $hashedValue;

    #[Column(type: 'datetime')]
    private DateTimeInterface $createdAt;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $expiresAt = null;

    #[Column(type: 'blob')]
    private $payload;

    private ?array $normalizedPayload = null;

    public function __construct(
        string $id,
        string $secretValue,
        array $payload,
        DateTimeInterface $createdAt,
        ?DateTimeInterface $expiresAt = null
    ) {
        $this->id = $id;

        $this->secretValue = $secretValue;
        $this->hashedValue = hash('sha512', $secretValue);

        $this->createdAt = $createdAt;
        $this->expiresAt = $expiresAt;

        $this->payload = json_encode($payload);
    }

    public function setSecretValue(string $value): void
    {
        $this->secretValue = $value;
    }

    /** @inheritDoc */
    public function getID(): string
    {
        return sprintf('%s:%s', $this->id, $this->secretValue);
    }

    public function getHashedValue(): string
    {
        return $this->hashedValue;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    /** @inheritDoc */
    public function getExpiresAt(): ?DateTimeInterface
    {
        return $this->expiresAt;
    }

    /** @inheritDoc */
    public function getPayload(): array
    {
        if (is_array($this->normalizedPayload)) {
          return $this->normalizedPayload;
        }

        if (is_resource($this->payload)) {
            // postgres
            $this->normalizedPayload = json_decode(stream_get_contents($this->payload), true);
        } elseif (is_string($this->payload)) {
            $this->normalizedPayload = json_decode($this->payload, true);
        }

        if ($this->normalizedPayload === null) {
            throw new TokenStorageException('Token payload is not valid!');
        }

        return $this->normalizedPayload;
    }
}
