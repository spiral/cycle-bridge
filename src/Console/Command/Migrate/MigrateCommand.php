<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\Migrate;

use Symfony\Component\Console\Input\InputOption;

final class MigrateCommand extends AbstractCommand
{
    protected const NAME = 'migrate';
    protected const DESCRIPTION = 'Execute one or multiple migrations.';
    protected const OPTIONS = [
        ['one', 'o', InputOption::VALUE_NONE, 'Execute only one (first) migration'],
    ];

    /**
     * Execute one or multiple migrations.
     */
    public function perform(): int
    {
        if (!$this->verifyEnvironment()) {
            return self::FAILURE;
        }

        $this->migrator->configure();

        $found = false;
        $count = $this->option('one') ? 1 : PHP_INT_MAX;

        while ($count > 0 && ($migration = $this->migrator->run())) {
            $found = true;
            $count--;

            $this->sprintf(
                "<info>Migration <comment>%s</comment> was successfully executed.</info>\n",
                $migration->getState()->getName(),
            );
        }

        if (!$found) {
            $this->error('No outstanding migrations were found.');
        }

        return self::SUCCESS;
    }
}
