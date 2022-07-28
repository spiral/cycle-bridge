<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Declaration\Entity;

use Spiral\Cycle\Scaffolder\Declaration\Entity\AnnotatedDeclaration;
use Spiral\Scaffolder\Config\ScaffolderConfig;
use Spiral\Tests\BaseTest;

final class AnnotatedDeclarationTest extends BaseTest
{
    /**
     * @dataProvider fieldsProvider
     */
    public function testAddField(string $name, string $accessibility, string $type, string $expectedType): void
    {
        $declaration = new AnnotatedDeclaration($this->getContainer()->get(ScaffolderConfig::class), 'test');

        $property = $declaration->addField($name, $accessibility, $type);

        $this->assertSame($name, $property->getName());
        $this->assertSame($accessibility, $property->getVisibility()->value);
        $this->assertSame($expectedType, $property->getType());
    }

    public function fieldsProvider(): \Traversable
    {
        yield ['primaryField', 'public', 'primary', 'int'];
        yield ['bigPrimaryField', 'private', 'bigPrimary', 'int'];
        yield ['integerField', 'protected', 'integer', 'int'];
        yield ['tinyIntegerField', 'public', 'tinyInteger', 'int'];
        yield ['smallIntegerField', 'protected', 'smallInteger', 'int'];
        yield ['bigIntegerField', 'public', 'bigInteger', 'int'];
        yield ['booleanField', 'public', 'boolean', 'bool'];
        yield ['doubleField', 'protected', 'double', 'float'];
        yield ['doubleField', 'public', 'double', 'float'];
        yield ['floatField', 'private', 'float', 'float'];
        yield ['decimalField', 'public', 'decimal', 'float'];
        yield ['dateField', 'protected', 'date', \DateTimeImmutable::class];
        yield ['timeField', 'public', 'time', \DateTimeImmutable::class];
        yield ['timestampField', 'private', 'timestamp', \DateTimeImmutable::class];
        yield ['enumField', 'public', 'enum', 'string'];
        yield ['stringField', 'public', 'string', 'string'];
        yield ['textField', 'private', 'text', 'string'];
    }
}
