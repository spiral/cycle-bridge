<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\Annotated\Entities;
use Cycle\ORM\SchemaInterface;
use Cycle\Schema\GeneratorInterface;
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
}
