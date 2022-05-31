<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth;

use Spiral\Auth\Exception\TokenStorageException;
use Spiral\Cycle\Auth\Token;
use Spiral\Tests\BaseTest;

final class TokenTest extends BaseTest
{
    public function testGetPayloadWithString(): void
    {
        $payload = ['key' => 'val'];

        $token = $this->buildToken($payload);

        $this->assertEquals($payload, $token->getPayload());
        $this->assertEquals($payload, $token->getPayload());
        $this->assertEquals($payload, $token->getPayload());
    }

    /**
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testGetPayloadWithBadPayload(): void
    {
        $brokenPayload = '{"some": "val"]';

        $token = $this->setProtectedProperty($this->buildToken(), 'payload', $brokenPayload);

        $this->expectException(TokenStorageException::class);
        $this->expectExceptionMessage('Token payload is not valid!');

        $token->getPayload();
    }

    /**
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testGetPayloadWithBadResourcePayload(): void
    {
        $resourcePayload = fopen('php://memory', 'r');

        $token = $this->setProtectedProperty($this->buildToken(), 'payload', $resourcePayload);

        $this->expectException(TokenStorageException::class);
        $this->expectExceptionMessage('Token payload is not valid!');

        $token->getPayload();
    }

    /**
     * @param object $object
     * @param string $property
     * @param $value
     *
     * @return object
     *
     * @throws \ReflectionException
     */
    protected function setProtectedProperty(object $object, string $property, $value): object
    {
        $refProjectClass = new \ReflectionClass(get_class($object));
        $classProperty = $refProjectClass->getProperty($property);
        $classProperty->setAccessible(true);
        $classProperty->setValue($object, $value);

        return $object;
    }

    private function buildToken(array $payload = []): Token
    {
        return new Token(
            '1',
            'secret',
            $payload,
            new \DateTimeImmutable()
        );
    }
}
