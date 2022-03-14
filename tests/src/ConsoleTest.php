<?php

declare(strict_types=1);

namespace Spiral\Tests;

abstract class ConsoleTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanupMigrations();
    }

    private function cleanupMigrations(): void
    {
        $this->cleanupDirectories($this->getDirectoryByAlias('migrations'));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanupMigrations();
    }
}
