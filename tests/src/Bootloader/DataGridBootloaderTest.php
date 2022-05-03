<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Spiral\DataGrid\Compiler;
use Spiral\DataGrid\WriterInterface;
use Spiral\Tests\BaseTest;
use Spiral\Tests\ConfigAttribute;

final class DataGridBootloaderTest extends BaseTest
{
    public function testGetsCompilerWithDefaultWriters(): void
    {
        $this->assertInstanceOf(
            Compiler::class,
            $compiler = $this->getContainer()->get(Compiler::class)
        );

        $writers = $this->accessProtected($compiler, 'writers');

        $this->assertCount(2, $writers);
        $this->assertContainsOnlyInstancesOf(WriterInterface::class, $writers);
    }

    #[ConfigAttribute(path: 'dataGrid.writers', value: [])]
    public function testGetsCompilerWithWritersFromConfig(): void
    {
        $this->assertInstanceOf(
            Compiler::class,
            $compiler = $this->getContainer()->get(Compiler::class)
        );

        $writers = $this->accessProtected($compiler, 'writers');

        $this->assertCount(0, $writers);
    }
}
