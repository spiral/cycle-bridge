<?php

declare(strict_types=1);

namespace Spiral\Tests;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Files\Files;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ConsoleTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanupMigrations();
    }

    public function runCommand(
        string $command,
        array $args = [],
        OutputInterface $output = null,
        ?int $verbosityLevel = null
    ): string {
        $input = new ArrayInput($args);
        $output = $output ?? new BufferedOutput();
        if ($verbosityLevel !== null) {
            $output->setVerbosity($verbosityLevel);
        }

        $this->app->console()->run($command, $input, $output);

        return $output->fetch();
    }

    public function runCommandDebug(string $command, array $args = [], OutputInterface $output = null): string
    {
        return $this->runCommand(
            command: $command,
            args: $args,
            output: $output,
            verbosityLevel: BufferedOutput::VERBOSITY_VERBOSE
        );
    }

    public function runCommandVeryVerbose(string $command, array $args = [], OutputInterface $output = null): string
    {
        return $this->runCommand(
            command: $command,
            args: $args,
            output: $output,
            verbosityLevel: BufferedOutput::VERBOSITY_DEBUG
        );
    }

    private function cleanupMigrations(): void
    {
        $dirs = $this->app->get(DirectoriesInterface::class);
        $migrations = $dirs->get('migrations');

        $fs = new Files();
        if ($fs->isDirectory($migrations)) {
            $fs->deleteDirectory($migrations);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanupMigrations();
    }
}
