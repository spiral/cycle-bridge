<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\Migrate;

use Spiral\Console\Command;
use Cycle\Migrations\Config\MigrationConfig;
use Cycle\Migrations\Migrator;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;

abstract class AbstractCommand extends Command
{
    public function __construct(
        protected Migrator $migrator,
        protected MigrationConfig $config,
    ) {
        parent::__construct();
    }

    protected function verifyConfigured(): bool
    {
        if (!$this->migrator->isConfigured()) {
            $this->writeln(
                "<fg=red>Migrations are not configured yet, run '<info>migrate:init</info>' first.</fg=red>",
            );

            return false;
        }

        return true;
    }

    /**
     * Check if current environment is safe to run migration.
     */
    protected function verifyEnvironment(string $message = 'Confirmation is required to run migrations!'): bool
    {
        if ($this->isForce() || $this->config->isSafe()) {
            //Safe to run
            return true;
        }

        $this->error($message);

        if (!$this->askConfirmation() || !$this->isInteractive()) {
            $this->comment('Cancelling operation...');

            return false;
        }

        return true;
    }

    protected function defineOptions(): array
    {
        return array_merge(
            static::OPTIONS,
            [
                ['force', 's', InputOption::VALUE_NONE, 'Skip safe environment check'],
                ['no-interaction', 'n', InputOption::VALUE_NONE, 'Do not ask any interactive question'],
            ],
        );
    }

    protected function askConfirmation(): bool
    {
        $question = new QuestionHelper();

        return $question->ask(
            $this->input,
            $this->output,
            new ConfirmationQuestion('<question>Would you like to continue?</question> ', false),
        );
    }

    protected function isInteractive(): bool
    {
        return !$this->option('no-interaction');
    }

    protected function isForce(): bool
    {
        return $this->option('force');
    }
}
