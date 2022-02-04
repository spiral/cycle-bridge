<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Cycle\Annotated\Entities;
use Cycle\ORM\SchemaInterface;
use Cycle\Schema\GeneratorInterface;
use Spiral\Cycle\Bootloader\SchemaBootloader;
use Spiral\Cycle\Config\CycleConfig;
use Spiral\Tests\BaseTest;

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

    public function testGetsSchemaGeneratorsOverrideByConfig(): void
    {
        $this->updateConfig('cycle.schema.generators', [
            Entities::class,
        ]);

        $generators = $this->bootloader->getGenerators($this->getContainer()->get(CycleConfig::class));

        $this->assertCount(1, $generators);
        $this->assertContainsOnlyInstancesOf(GeneratorInterface::class, $generators);
    }

    public function testGetsSchemaGeneratorsOverrideByConfigWithEmptyArray(): void
    {
        $this->updateConfig('cycle.schema.generators', []);

        $generators = $this->bootloader->getGenerators($this->getContainer()->get(CycleConfig::class));

        $this->assertCount(0, $generators);
    }
}
