<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\Annotated\Entities;
use Cycle\ORM\Mapper\Mapper;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Select\Repository;
use Cycle\ORM\Select\Source;
use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Registry;
use Spiral\Cycle\Bootloader\SchemaBootloader;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Tests\BaseTest;
use Spiral\Tests\ConfigAttribute;

final class SchemaBootloaderTest extends BaseTest
{
    private SchemaBootloader $bootloader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootloader = $this->getContainer()->get(SchemaBootloader::class);
    }

    public function testGetsSchema(): void
    {
        $this->assertContainerBound(SchemaInterface::class);
    }

    public function testGetsDefaultSchemaGenerators(): void
    {
        $generators = $this->bootloader->getGenerators($this->getContainer()->get(CycleConfig::class));

        $this->assertCount(14, $generators);
        $this->assertContainsOnlyInstancesOf(GeneratorInterface::class, $generators);
    }

    #[ConfigAttribute(path: 'cycle.schema.generators', value: [Entities::class])]
    public function testGetsSchemaGeneratorsOverrideByConfig(): void
    {
        $generators = $this->bootloader->getGenerators($this->getContainer()->get(CycleConfig::class));

        $this->assertCount(1, $generators);
        $this->assertContainsOnlyInstancesOf(GeneratorInterface::class, $generators);
    }

    #[ConfigAttribute(path: 'cycle.schema.generators', value: [])]
    public function testGetsSchemaGeneratorsOverrideByConfigWithEmptyArray(): void
    {
        $generators = $this->bootloader->getGenerators($this->getContainer()->get(CycleConfig::class));

        $this->assertCount(0, $generators);
    }

    public function testRegistryWithDefaultConfig(): void
    {
        $defaults = $this->getContainer()->get(Registry::class)->getDefaults();

        $this->assertSame(Mapper::class, $defaults[SchemaInterface::MAPPER]);
        $this->assertSame(Repository::class, $defaults[SchemaInterface::REPOSITORY]);
        $this->assertSame(Source::class, $defaults[SchemaInterface::SOURCE]);
        $this->assertNull($defaults[SchemaInterface::SCOPE]);
        $this->assertNull($defaults[SchemaInterface::TYPECAST_HANDLER]);
    }

    #[ConfigAttribute(path: 'cycle.schema.defaults', value: [SchemaInterface::TYPECAST_HANDLER => ['foo', 'bar']])]
    public function testRegistryWithModifiedConfig(): void
    {
        $defaults = $this->getContainer()->get(Registry::class)->getDefaults();

        $this->assertSame(Mapper::class, $defaults[SchemaInterface::MAPPER]);
        $this->assertSame(Repository::class, $defaults[SchemaInterface::REPOSITORY]);
        $this->assertSame(Source::class, $defaults[SchemaInterface::SOURCE]);
        $this->assertNull($defaults[SchemaInterface::SCOPE]);
        $this->assertSame(['foo', 'bar'], $defaults[SchemaInterface::TYPECAST_HANDLER]);
    }
}
